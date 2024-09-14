<?php

namespace App\Security;

use App\Repository\AccessTokenRepository;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        protected readonly AccessTokenRepository $accessTokenRepository
    ) {}

    /**
     * @throws AuthenticationException
     */
    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        $accessToken = $this->accessTokenRepository->getByRawToken($accessToken);

        if (is_null($accessToken)) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        return new UserBadge((string) $accessToken->getUserId());
    }
}