<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SystemRoleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Defines the system roles (Admin, Treasurer, etc.).
 */
#[ORM\Entity(repositoryClass: SystemRoleRepository::class)]
#[ORM\Table(name: 'system_roles')]
#[ORM\UniqueConstraint(name: 'UNIQ_SYSTEM_ROLES_ROLE_NAME', columns: ['role_name'])]
class SystemRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')] // Use IDENTITY for SERIAL columns in PostgreSQL/MySQL
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * Unique technical name of the role (e.g., ROLE_CHURCH_TREASURER, ROLE_ADMIN).
     * Must start with ROLE_ for Symfony Security.
     */
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, nullable: false)]
    private ?string $roleName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    // --- Getters and Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function setRoleName(string $roleName): static
    {
        $this->roleName = $roleName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}