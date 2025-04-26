<?php
// filepath: tests/DatabaseTestCase.php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Base class for tests needing database interaction and reset.
 */
abstract class DatabaseTestCase extends WebTestCase
{
    // Traits needed for Foundry 2.x
    use ResetDatabase;
    use Factories;
}