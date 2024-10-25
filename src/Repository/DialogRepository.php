<?php

namespace App\Repository;

use App\Model\ModelInterface;
use App\Model;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @phpstan-type RawDialog array{id: int, uuid: string, is_groupchat: bool, created_at: string}
 *
 * @template-extends BaseRepository<RawDialog, Model\Dialog>
 */
#[ModelClass(Model\Dialog::class)]
class DialogRepository extends BaseRepository
{
    public function __construct(
        protected readonly RepositoryMapperInterface $repositoryMapper,
        Connection $dbDialogConnection,
        Connection $slaveDialogConnection
    ) {
        parent::__construct($dbDialogConnection, $slaveDialogConnection);
    }

    public function create(
        bool $isGroupChat
    ): Model\Dialog {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        return new Model\Dialog(
            null,
            Uuid::uuid7($now),
            $now,
            $isGroupChat
        );
    }

    public function getPMForUsers(int $userId, int $otherUserId): ?Model\Dialog
    {
        $sql = <<<'SQL'
        SELECT d."uuid" FROM app_dialogs AS d 
        INNER JOIN app_dialog_participants AS dp1 ON d.id=dp1.dialog_id
        INNER JOIN app_dialog_participants AS dp2 ON d.id=dp2.dialog_id
        WHERE dp1.user_id=:user_id AND dp2.user_id=:other_user_id AND d.is_groupchat=:is_groupchat
SQL;
        $dialogUUID = $this->getConnection()->fetchOne(
            $sql,
            [
                'user_id' => $userId,
                'other_user_id' => $otherUserId,
                'is_groupchat' => false,
            ],
            [
                'user_id' => Types::INTEGER,
                'other_user_id' => Types::INTEGER,
                'is_groupchat' => Types::BOOLEAN,
            ]
        );
        if (empty($dialogUUID)) {
            return null;
        }

        $dialog = $this->getByUUID(Uuid::fromString($dialogUUID));
        assert(!empty($dialog));

        return $dialog;
    }

    public function getByUUID(UuidInterface $uuid): ?Model\Dialog
    {
        /** @var RawDialog $rawData */
        $rawData = $this->getConnection()->fetchAssociative(
            'SELECT * FROM app_dialogs WHERE "uuid"=:uuid',
            ['uuid' => $uuid->toString()]
        );

        return $this->hydrate($rawData);
    }

    public function insert(Model\Dialog $dialog): Model\Dialog
    {
        $isMaster = true;
        $sql = <<<'SQL'
        INSERT INTO app_dialogs ("uuid", is_groupchat, created_at) VALUES (:uuid, :is_groupchat, :created_at)
SQL;
        $this->beginTransaction($isMaster);
        $this->getConnection($isMaster)->executeQuery(
            $sql,
            [
                'uuid' => $dialog->getUuid()->toString(),
                'is_groupchat' => $dialog->isGroupchat(),
                'created_at' => $dialog->getCreatedAt()->format('c')
            ],
            [
                'uuid' => Types::STRING,
                'is_groupchat' => Types::BOOLEAN,
                'created_at' => Types::STRING,
            ]
        );
        $this->commitTransaction($isMaster);
        $res = $this->getByUUID($dialog->getUuid());
        assert(!empty($res));

        return $res;
    }

    protected function hydrate(bool|array $rawData): ?ModelInterface
    {
        if ($this->isEmptyRawData($rawData)) {
            return null;
        }

        return new Model\Dialog(
            $rawData['id'],
            Uuid::fromString($rawData['uuid']),
            new \DateTimeImmutable($rawData['created_at']),
            $rawData['is_groupchat']
        );
    }
}
