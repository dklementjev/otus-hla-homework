<?php

namespace App\Repository;

use App\Model\DialogUser;
use App\Model\ModelInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @phpstan-type RawDialogUser array{id: int, nickname: string, uuid: string}
 *
 * @template-extends BaseRepository<RawDialogUser, DialogUser>
 */
#[ModelClass(DialogUser::class)]
class DialogUserRepository extends BaseRepository
{
    public function __construct(
        protected readonly RepositoryMapperInterface $repositoryMapper,
        Connection $dbDialogConnection,
        Connection $slaveDialogConnection
    ) {
        parent::__construct($dbDialogConnection, $slaveDialogConnection);
    }

    public function create(
        ?int $id,
        ?UuidInterface $uuid,
        ?string $nickname
    ): DialogUser {
        return new DialogUser(
            $id,
            $uuid ?? Uuid::uuid7(),
            $nickname
        );
    }

    public function getById(int $id, $isMaster = false): ?DialogUser
    {
        return $this->hydrate(
            $this->getConnection($isMaster)
                ->executeQuery(
                    'SELECT * FROM app_dialog_users AS du WHERE du.id=:id',
                    ['id' => $id]
                )
                ->fetchAssociative()
        );
    }

    public function getByUUID(UuidInterface $uuid, bool $isMaster = false): ?DialogUser
    {
        return $this->hydrate(
            $this->getConnection($isMaster)
                ->executeQuery(
                    'SELECT * FROM app_dialog_users AS du WHERE du."uuid"=:uuid',
                    ['uuid' => $uuid->toString()]
                )
            ->fetchAssociative()
        );
    }

    public function getOrCreateByUserId(int $userId): DialogUser
    {
        $res = $this->getById($userId, true);
        if (empty($res)) {
            $res = $this->insert($this->create($userId, null, null));
        }

        return $res;
    }

    public function insert(DialogUser $model): DialogUser
    {
        $isMaster = true;
        $this->beginTransaction($isMaster);
        $rowCount = $this->getConnection($isMaster)
            ->executeStatement(
                'INSERT INTO app_dialog_users (id, uuid, nickname) VALUES (:id, :uuid, :nickname)',
                [
                    'id' => $model->getId(),
                    'uuid' => $model->getUuid()->toString(),
                    'nickname' => $model->getNickname(),
                ]
            )
        ;
        $this->commitTransaction($isMaster);
        if ($rowCount<1) {
            throw new \LogicException();
        }

        return $this->getByUUID($model->getUuid());
    }

    protected function hydrate(bool|array $rawData): ?ModelInterface
    {
        if ($this->isEmptyRawData($rawData)) {
            return null;
        }

        return $this->create(
            $rawData['id'],
            Uuid::fromString($rawData['uuid']),
            $rawData['nickname']
        );
    }
}
