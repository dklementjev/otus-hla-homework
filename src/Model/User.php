<?php

namespace App\Model;

use App\Security\UserRole;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

class User implements PasswordAuthenticatedUserInterface, UserInterface, ModelInterface
{
    #[Groups('default_view')]
    #[SerializedName('first_name')]
    protected string $firstName;

    #[Groups('default_view')]
    #[SerializedName('second_name')]
    protected string $lastName;

    #[Groups('default_view')]
    protected \DateTimeInterface $birthdate;

    #[Groups('default_view')]
    #[SerializedName('biography')]
    protected string $bio;

    #[Groups('default_view')]
    protected string $city;

    protected string $passwordHash;

    public function __construct(
        #[Groups(['register_view'])]
        #[SerializedName('id')]
        protected readonly ?int $id = null
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $value): self
    {
        $this->firstName = $value;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $value): self
    {
        $this->lastName = $value;

        return $this;
    }

    public function getBirthdate(): \DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $value): self
    {
        $this->birthdate = $value;

        return $this;
    }

    public function getBio(): string
    {
        return $this->bio;
    }

    public function setBio(string $value): self
    {
        $this->bio = $value;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $value): self
    {
        $this->city = $value;

        return $this;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $value): self
    {
        $this->passwordHash = $value;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->getPasswordHash();
    }

    public function getRoles(): array
    {
        return [
            UserRole::User->value,
        ];
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->getId();
    }

    public function eraseCredentials()
    {
        // NOP
    }
}
