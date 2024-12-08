<?php

namespace App\Utils\Model\Dialog;

use App\Model\Dialog as DialogModel;
use App\Model\DialogMessage as DialogMessageModel;
use App\Utils\Model\DialogInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tarantool\Client\Client;

class TarantoolImplementation implements DialogInterface
{
    public function __construct(
        protected readonly Client $tarantoolClient,
        protected readonly LoggerInterface $logger
    ) {}

    public function getPMForUsers(int $userId, int $otherUserId): ?DialogModel
    {
        $rawRes = $this->callUDF('box.space.dialogs:getPMForUsers', [$userId, $otherUserId], __METHOD__);

        if (count($rawRes)<1) {
            return null;
        }

        return $this->dialogFromTarantoolData($rawRes[0]);
    }

    public function createPMForUsers(int $userId, int $otherUserId): DialogModel
    {
        $rawRes = $this->callUDF(
            'box.space.dialogs:createPMForUsers',
            [
                $userId,
                $otherUserId
            ],
            __METHOD__
        );

        if (count($rawRes)<1) {
            throw new \Exception("PM dialog creation failed");
        }

        return $this->dialogFromTarantoolData($rawRes[0]);
    }

    public function getOrCreatePMForUsers(int $userId, int $otherUserId): DialogModel
    {
        return $this->getPMForUsers($userId, $otherUserId) ?? $this->createPMForUsers($userId, $otherUserId);
    }

    public function createMessage(int $userId, int $dialogId, string $text): ?DialogMessageModel
    {
        $rawRes = $this->callUDF(
            'box.space.messages:create',
            [
                (string)(Uuid::uuid7()),
                $userId,
                $dialogId,
                $text,
                (new \DateTime())->format("c")
            ],
            __METHOD__
        );

        if (count($rawRes)<1) {
            return null;
        }

        return $this->messageFromTarantoolData($rawRes[0]);
    }

    public function getRecentMessages(int $dialogId, int $limit=100): array
    {
        $rawRes = $this->callUDF('box.space.messages:findByDialogId', [$dialogId, $limit], __METHOD__);
        if (count($rawRes)<1) {
            return [];
        }

        return array_map(
            fn (array $rawDialogMessage) => $this->messageFromTarantoolData($rawDialogMessage),
            $rawRes[0]
        );
    }
    
    private function dialogFromTarantoolData(?array $rawDialog): ?DialogModel
    {
        if (is_null($rawDialog)) {
            return null;
        }

        return new DialogModel(
            $rawDialog['id'],
            Uuid::fromString($rawDialog['uuid']),
            new \DateTimeImmutable($rawDialog['created_at']),
            $rawDialog['is_groupchat']
        );
    }

    private function messageFromTarantoolData(array $rawMessage): DialogMessageModel
    {
        return new DialogMessageModel(
            $rawMessage['id'],
            Uuid::fromString($rawMessage['uuid']),
            $rawMessage['user_id'],
            $rawMessage['dialog_id'],
            $rawMessage['message'],
            new \DateTimeImmutable($rawMessage['created_at']),
        );
    }

    private function callUDF(string $functionName, array $args, ?string $logTag = null): array
    {
        $msg = 'callUDF:' . ($logTag ?? '');
        $this->logger->debug($msg, ['function' => $functionName, 'args' => $args]);
        $res = $this->tarantoolClient->call($functionName, ...$args);
        $this->logger->debug($msg, ['res'=>$res]);

        return $res;
    }

    private function evaluateLua(string $luaCode, array $args, ?string $logTag = null): array
    {
        $msg = 'evaluateLua:' . ($logTag ?? '');
        $this->logger->debug($msg, ['lua' => $luaCode, 'args' => $args]);
        $res = $this->tarantoolClient->evaluate($luaCode, ...$args);
        $this->logger->debug($msg, ['res'=>$res]);

        return $res;
    }
}
