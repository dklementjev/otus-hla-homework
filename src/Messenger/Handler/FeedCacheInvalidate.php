<?php

namespace App\Messenger\Handler;

use App\Messenger\Message;
use App\Utils;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
final class FeedCacheInvalidate
{
    public function __construct(
        protected readonly Utils\Model\Post $postUtils
    ) {
    }

    public function __invoke(Message\FeedCacheInvalidate $message): void
    {
        $this->postUtils->invalidateFeed($message->getUserId());
    }
}
