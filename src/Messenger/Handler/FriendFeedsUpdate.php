<?php

namespace App\Messenger\Handler;

use App\Utils;
use App\Messenger\Message;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler()]
final class FriendFeedsUpdate
{
    public function __construct(
        protected readonly MessageBusInterface $messageBus,
        protected readonly Utils\Model\UserFriend $userFriendUtils
    ) {
    }

    public function __invoke(Message\FriendFeedsUpdate $message): void
    {
        $userIds = $this->userFriendUtils->findUserIdsByFriendId($message->getUserId());

        foreach ($userIds as $userId) {
            $this->messageBus->dispatch(new Message\FeedCacheInvalidate($userId));
        }
    }
}
