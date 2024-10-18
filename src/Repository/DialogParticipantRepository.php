<?php

namespace App\Repository;

use App\Model\DialogParticipant;
use App\Model\ModelInterface;
use Doctrine\DBAL\Connection;

/**
 * @phpstan-type RawDialogParticipant array{dialog_id: int, user_id: int}
 *
 * @template-extends BaseRepository<RawDialogParticipant, DialogParticipant>
 */
#[ModelClass(DialogParticipant::class)]
class DialogParticipantRepository extends BaseRepository
{
    public function __construct(
        Connection $dbDialogConnection,
        Connection $slaveDialogConnection
    ) {
        parent::__construct($dbDialogConnection, $slaveDialogConnection);
    }

    public function create(
        int $dialogId,
        int $userId
    ): DialogParticipant {
        return new DialogParticipant(
            $dialogId,
            $userId
        );
    }

    public function insert(DialogParticipant $model): DialogParticipant
    {
        $this->getConnection(true)->executeQuery(
            'INSERT INTO app_dialog_participants (dialog_id, user_id) VALUES (:dialog_id, :user_id)',
            [
                'dialog_id' => $model->getDialogId(),
                'user_id' => $model->getUserId(),
            ]
        );

        return $model;
    }

    protected function hydrate(bool|array $rawData): ?ModelInterface
    {
        if ($this->isEmptyRawData($rawData)) {
            return null;
        }

        return new DialogParticipant(
            $rawData['dialog_id'],
            $rawData['user_id']
        );
    }
}
