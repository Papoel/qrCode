<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private ?string $email = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING)]
    private string $password;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $lastname = null;

    /**
     * @var Collection<int, QrcodeEntity>
     */
    #[ORM\OneToMany(targetEntity: QrcodeEntity::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $qrcodes;

    public function __construct()
    {
        $this->qrcodes = new ArrayCollection();
    }

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
     * @return array|string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->firstname, $this->lastname);
    }

    /**
     * @return Collection<int, QrcodeEntity>
     */
    public function getQrcodes(): Collection
    {
        return $this->qrcodes;
    }

    public function addQrcode(QrcodeEntity $qrcode): static
    {
        if (!$this->qrcodes->contains($qrcode)) {
            $this->qrcodes->add($qrcode);
            $qrcode->setUser($this);
        }

        return $this;
    }

    public function removeQrcode(QrcodeEntity $qrcode): static
    {
        if ($this->qrcodes->removeElement($qrcode)) {
            // set the owning side to null (unless already changed)
            if ($qrcode->getUser() === $this) {
                $qrcode->setUser(null);
            }
        }

        return $this;
    }
}
