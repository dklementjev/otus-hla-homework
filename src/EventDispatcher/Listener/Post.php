<?php

declare(strict_types=1);

namespace App\EventDispatcher\Listener;

use App\EventDispatcher\Event;
use App\EventDispatcher\EventType;
use App\Messenger\Message\FriendFeedsUpdate;
use App\Messenger\Message\PostBroadcast;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

class Post
{
    public function __construct(
        protected readonly MessageBusInterface $messageBus
    ) {}

    #[AsEventListener(event: EventType\Post::Update->value)]
    public function updateHandler(Event\Post $event)
    {
        $this->messageBus->dispatch(new FriendFeedsUpdate($event->getPost()->getUserId()));
    }

    #[AsEventListener(event: EventType\Post::Insert->value)]
    public function insertHandler(Event\Post $event)
    {
        $post = $event->getPost();

        $this->messageBus->dispatch(new FriendFeedsUpdate($post->getUserId()));
        $this->messageBus->dispatch(new PostBroadcast($post->getUserId(), $post->getUUID(), $post->getText()));
    }
}
