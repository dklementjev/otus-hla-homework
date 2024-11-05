<?php

namespace App\Messenger\Handler;

use App\Messenger;
use App\Messenger\Message\UserNotification;
use App\Utils\Model\UserFriend;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler()]
final readonly class PostBroadcast
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private UserFriend $userFriendUtils
    ) {}

    public function __invoke(Messenger\Message\PostBroadcast $message): void
    {
        $userIds = $this->userFriendUtils->findUserIdsByFriendId($message->getUserId());

        foreach ($userIds as $friendId) {
            $this->messageBus->dispatch(
                new UserNotification($message->getUserId(), 'added', ['id' => $message->getPostId()]),
                [
                    new AmqpStamp('user_notification.post.'.$friendId),
                ]
            );
        }
    }
}
