<?php

namespace App\Utils\Model;

use App\Model\Dialog as DialogModel;
use App\Model\DialogMessage;

interface DialogInterface
{
    public function getPMForUsers(int $userId, int $otherUserId): ?DialogModel;

    public function createPMForUsers(int $userId, int $otherUserId): DialogModel;

    public function getOrCreatePMForUsers(int $userId, int $otherUserId): DialogModel;

    public function createMessage(int $userId, int $dialogId, string $text): ?DialogMessage;

    /**
     * @return DialogMessage[]
     */
    public function getRecentMessages(int $dialogId): array;
}
