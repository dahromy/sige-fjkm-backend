<?php
// filepath: tests/Integration/SystemRoleTest.php
declare(strict_types=1);

namespace App\Tests\Integration;

use App\Factory\SystemRoleFactory;
use App\Repository\SystemRoleRepository;
use App\Tests\DatabaseTestCase;

class SystemRoleTest extends DatabaseTestCase
{
    public function testSystemRoleCanBeCreatedWithFactory(): void
    {
        // Create a SystemRole using the factory with a unique role name
        $role = SystemRoleFactory::createOne([
            'roleName' => 'ROLE_TEST_ADMIN_' . uniqid(),
            'description' => 'Test Admin Role Description',
        ]);

        // With Foundry 2.x, the proxy object can be used directly
        // Assert entity properties
        self::assertNotNull($role->getId());
        self::assertStringStartsWith('ROLE_TEST_ADMIN_', $role->getRoleName());
        self::assertSame('Test Admin Role Description', $role->getDescription());

        // Verify using repository
        $repository = static::getContainer()->get(SystemRoleRepository::class);
        $roleFromDb = $repository->find($role->getId());

        self::assertNotNull($roleFromDb);
        self::assertSame($role->getRoleName(), $roleFromDb->getRoleName());
    }

    public function testMultipleRolesCanBeCreated(): void
    {
        // Make sure we have a clean state before counting
        $repository = static::getContainer()->get(SystemRoleRepository::class);
        $initialCount = $repository->count([]);

        // Create roles with default random data
        SystemRoleFactory::createMany(3);

        // Verify count increased
        $newCount = $repository->count([]);
        self::assertSame($initialCount + 3, $newCount);
    }
}
