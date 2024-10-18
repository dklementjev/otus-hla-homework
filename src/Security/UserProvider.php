<?php

namespace App\Security;

use App\Model\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @template-implements UserProviderInterface<User>
 */
class UserProvider implements UserProviderInterface
{
    public function __construct(
        protected readonly UserRepository $userRepository
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $res = $this->userRepository->getById((int) $identifier);

        if ($res === null) {
            throw new UserNotFoundException();
        }

        return $res;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new \LogicException('Not implemented');
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
