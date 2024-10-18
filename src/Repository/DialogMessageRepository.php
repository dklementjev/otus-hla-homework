<?php

namespace App\Repository;

use App\Model\DialogMessage;
use App\Model\ModelInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @phpstan-type RawDialogMessage array{id: int, uuid: string, user_id: int, dialog_id: int, message: string, created_at: string}
 *
 * @template-extends BaseRepository<RawDialogMessage, DialogMessage>
 */
#[ModelClass(DialogMessage::class)]
class DialogMessageRepository extends BaseRepository
{
    public function __construct(
        Connection $dbDialogConnection,
        Connection $slaveDialogConnection
    ) {
        parent::__construct($dbDialogConnection, $slaveDialogConnection);
    }

    public function create(
        int $userId,
        int $dialogId,
        string $message
    ): DialogMessage {
        return new DialogMessage(
            null,
            Uuid::uuid7(),
            $userId,
            $dialogId,
            $message,
            new \DateTimeImmutable()
        );
    }

    public function getById(int $id, bool $isMaster = false): ?DialogMessage
    {
        return $this->hydrate(
            $this->getConnection($isMaster)
                ->executeQuery('SELECT * FROM app_dialog_messages WHERE id=:id', ['id' => $id])
                ->fetchAssociative()
        );
    }

    public function insert(
        DialogMessage $message
    ): DialogMessage {
        $connection = $this->getConnection(true);
        $connection->executeQuery(
            'INSERT INTO app_dialog_messages("uuid", user_id, dialog_id, message, created_at) '.
                    'VALUES (:uuid, :user_id, :dialog_id, :message, :created_at)',
            [
                'uuid' => $message->getUuid()->toString(),
                'user_id' => $message->getUserId(),
                'dialog_id' => $message->getDialogId(),
                'message' => $message->getMessage(),
                'created_at' => $message->getCreatedAt()->format('c'),
            ]
        );
        $id = $connection->lastInsertId();
        $dialogMessage = $this->getById($id, true);
        assert(!empty($dialogMessage));

        return $dialogMessage;
    }

    /**
     * @param positive-int $limit
     *
     * @return DialogMessage[]
     */
    public function findByDialog(int $dialogId, int $limit = 100): array
    {
        return $this->hydrateAll(
            $this->getConnection()->executeQuery(
                'SELECT * FROM app_dialog_messages WHERE dialog_id=:dialog_id LIMIT :limit',
                ['dialog_id' => $dialogId, 'limit' => $limit]
            )
            ->fetchAllAssociative()
        );
    }

    public function findRecentByDialogId(int $dialogId, int $limit = 100): array
    {
        return $this->hydrateAll(
            $this->getConnection()->executeQuery(
                'SELECT * FROM app_dialog_messages WHERE dialog_id=:dialog_id ORDER BY created_at DESC LIMIT :limit',
                ['dialog_id' => $dialogId, 'limit' => $limit]
            )
            ->fetchAllAssociative()
        );
    }

    /**
     * @param false|RawDialogMessage $rawData
     *
     * @return DialogMessage
     */
    protected function hydrate(bool|array $rawData): ?ModelInterface
    {
        if ($this->isEmptyRawData($rawData)) {
            return null;
        }

        return new DialogMessage(
            $rawData['id'],
            Uuid::fromString($rawData['uuid']),
            $rawData['user_id'],
            $rawData['dialog_id'],
            $rawData['message'],
            new \DateTimeImmutable($rawData['created_at'])
        );
    }
}
