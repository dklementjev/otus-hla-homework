<?php

namespace App\Utils\Model;

use App\Model\Dialog as DialogModel;
use App\Model\DialogMessage;
use App\Repository\DialogMessageRepository;
use App\Repository\DialogParticipantRepository;
use App\Repository\DialogRepository;
use App\Repository\DialogUserRepository;

class Dialog
{
    public function __construct(
        protected readonly DialogRepository $dialogRepository,
        protected readonly DialogUserRepository $dialogUserRepository,
        protected readonly DialogParticipantRepository $dialogParticipantRepository,
        protected readonly DialogMessageRepository $dialogMessageRepository
    ) {
    }

    public function getPMForUsers(int $userId, int $otherUserId): ?DialogModel
    {
        return $this->dialogRepository->getPMForUsers($userId, $otherUserId);
    }

    public function createPMForUsers(int $userId, int $otherUserId): DialogModel
    {
        $user = $this->dialogUserRepository->getOrCreateByUserId($userId);
        $otherUser = $this->dialogUserRepository->getOrCreateByUserId($otherUserId);
        $dialog = $this->dialogRepository->insert($this->dialogRepository->create(false));
        $this->dialogParticipantRepository->insert($this->dialogParticipantRepository->create($dialog->getId(), $user->getId()));
        $this->dialogParticipantRepository->insert($this->dialogParticipantRepository->create($dialog->getId(), $otherUser->getId()));

        return $dialog;
    }

    public function getOrCreatePMForUsers(int $userId, int $otherUserId): DialogModel
    {
        return $this->getPMForUsers($userId, $otherUserId) ?? $this->createPMForUsers($userId, $otherUserId);
    }

    public function createMessage(int $userId, int $dialogId, string $text): ?DialogMessage
    {
        $message = $this->dialogMessageRepository->create(
            $userId,
            $dialogId,
            $text
        );

        return $this->dialogMessageRepository->insert($message);
    }

    /**
     * @return DialogMessage[]
     */
    public function getRecentMessages(int $dialogId): array
    {
        return $this->dialogMessageRepository->findRecentByDialogId($dialogId);
    }
}
