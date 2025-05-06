<?php

namespace App\Factory;

use App\Entity\SystemRole;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<SystemRole>
 */
final class SystemRoleFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return SystemRole::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        // Generate a unique role name starting with ROLE_
        $baseRoleName = self::faker()->unique()->word();
        $roleName = 'ROLE_' . strtoupper(str_replace(' ', '_', $baseRoleName));
        // Ensure length constraint (adjust if faker generates very long words)
        $roleName = substr($roleName, 0, 100);

        return [
            'roleName' => $roleName,
            'description' => self::faker()->optional()->sentence(), // Optional description
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(SystemRole $systemRole): void {})
        ;
    }
}
