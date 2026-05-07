<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Tenant\TenantScopedInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\Index(columns: ['tenant_id'], name: 'idx_users_tenant_id')]
#[ORM\Index(columns: ['branch_id'], name: 'idx_users_branch_id')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TenantScopedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $tenantId;

    #[ORM\Column(length: 191)]
    private string $name;

    #[ORM\Column(length: 191, unique: true)]
    private string $email;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 191, options: ['default' => 'UTC'])]
    private string $timezone = 'UTC';

    #[ORM\Column(length: 10, options: ['default' => 'en'])]
    private string $language = 'en';

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $settings = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $emailVerifiedAt = null;

    #[ORM\Column(length: 191)]
    private string $password;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $rememberToken = null;

    #[ORM\Column(nullable: true)]
    private ?int $branchId = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'role_user')]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getTenantId(): int { return $this->tenantId; }
    public function setTenantId(int $tenantId): void { $this->tenantId = $tenantId; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getAvatar(): ?string { return $this->avatar; }
    public function setAvatar(?string $avatar): void { $this->avatar = $avatar; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): void { $this->phone = $phone; }

    public function getTimezone(): string { return $this->timezone; }
    public function setTimezone(string $timezone): void { $this->timezone = $timezone; }

    public function getLanguage(): string { return $this->language; }
    public function setLanguage(string $language): void { $this->language = $language; }

    public function getSettings(): ?array { return $this->settings; }
    public function setSettings(?array $settings): void { $this->settings = $settings; }

    public function getEmailVerifiedAt(): ?\DateTimeInterface { return $this->emailVerifiedAt; }
    public function setEmailVerifiedAt(?\DateTimeInterface $emailVerifiedAt): void { $this->emailVerifiedAt = $emailVerifiedAt; }

    public function getBranchId(): ?int { return $this->branchId; }
    public function setBranchId(?int $branchId): void { $this->branchId = $branchId; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }

    // --- UserInterface ---

    public function getUserIdentifier(): string { return $this->email; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): void { $this->password = $password; }

    public function eraseCredentials(): void {}

    /** @return string[] */
    public function getRoles(): array
    {
        $roleNames = ['ROLE_USER'];
        foreach ($this->roles as $role) {
            $roleNames[] = 'ROLE_' . strtoupper($role->getSlug());
        }
        return array_unique($roleNames);
    }

    public function getRoleEntities(): Collection { return $this->roles; }
    public function addRole(Role $role): void { if (!$this->roles->contains($role)) { $this->roles->add($role); } }
    public function removeRole(Role $role): void { $this->roles->removeElement($role); }

    public function hasRole(string $slug): bool
    {
        foreach ($this->roles as $role) {
            if ($role->getSlug() === $slug) return true;
        }
        return false;
    }
}
