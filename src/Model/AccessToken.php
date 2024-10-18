<?php

namespace App\Model;

class AccessToken implements ModelInterface
{
    protected int $userId;

    protected string $rawToken;

    public function __construct(
        protected readonly ?int $id = null
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $value): self
    {
        $this->userId = $value;

        return $this;
    }

    public function getRawToken(): string
    {
        return $this->rawToken;
    }

    public function setRawToken(string $value): self
    {
        $this->rawToken = $value;

        return $this;
    }
}
