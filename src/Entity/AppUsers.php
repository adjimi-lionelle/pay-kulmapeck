<?php

namespace App\Entity;

use App\Repository\AppUsersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AppUsersRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class AppUsers implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ ORM\Column( length: 255 ) ]
    private ?string $userName = null;

    #[ ORM\Column( length: 255 ) ]
    private ?string $surName = null;

    #[ ORM\Column( length: 10 ) ]
    private ?bool $enable = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?AppEnterprise $userEnterprise = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $phone = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    public function getUserName(): ?string {
        return $this->userName;
    }

    public function setUserName( string $userName ): static {
        $this->userName = $userName;

        return $this;
    }


    public function getSurName(): ?string {
        return $this->surName;
    }

    public function setSurName( string $surName ): static {
        $this->surName = $surName;

        return $this;
    }

    public function getEnable(): ?bool {
        return $this->enable;
    }

    public function setEnable( bool $enable ): static {
        $this->enable = $enable;

        return $this;
    }


    public function getUserEnterprise(): ?AppEnterprise
    {
        return $this->userEnterprise;
    }
    public function getSalt()
    {
        // You can return null if you're using modern password hashing methods
        // that don't require a separate salt.
        return null;
    }

    public function setUserEnterprise(?AppEnterprise $userEnterprise): static
    {
        $this->userEnterprise = $userEnterprise;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    
}
