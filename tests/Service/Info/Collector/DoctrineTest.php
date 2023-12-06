<?php

declare(strict_types=1);

/*
 * This file is part of the symsensor/actuator-doctrine-bundle package.
 *
 * (c) Kevin Studer <kreemer@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymSensor\ActuatorDoctrineBundle\Tests\Service\Info\Collector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use SymSensor\ActuatorDoctrineBundle\Service\Info\Collector\Doctrine;

class DoctrineTest extends TestCase
{
    /**
     * @test
     */
    public function nameWillBeDatabase(): void
    {
        self::assertEquals('doctrine', (new Doctrine([]))->collect()->name());
    }

    /**
     * @test
     */
    public function databaseWillReturnUnknownIfNoConnection(): void
    {
        // given
        $collection = new Doctrine(['default' => new \stdClass()]); // @phpstan-ignore-line

        // when
        $result = $collection->collect();

        // then
        self::assertFalse($result->isEmpty());
        $array = $result->jsonSerialize();

        self::assertArrayHasKey('default', $array);
        self::assertIsArray($array['default']);
        self::assertArrayHasKey('type', $array['default']);
        self::assertEquals('Unknown', $array['default']['type']);
        self::assertArrayHasKey('database', $array['default']);
        self::assertEquals('Unknown', $array['default']['database']);
        self::assertArrayHasKey('driver', $array['default']);
        self::assertEquals('Unknown', $array['default']['driver']);
    }

    /**
     * @test
     */
    public function databaseInformationIsDisplayed(): void
    {
        // given
        $connection = self::createMock(Connection::class);
        $driver = self::createMock(Driver::class);
        $databasePlatform = self::createMock(AbstractPlatform::class);

        $connection->method('getDatabasePlatform')
            ->willReturn($databasePlatform);

        $connection->method('getDriver')
            ->willReturn($driver);

        $collection = new Doctrine(['default' => $connection]);

        // when
        $result = $collection->collect();

        // then
        self::assertFalse($result->isEmpty());
        $array = $result->jsonSerialize();

        self::assertArrayHasKey('default', $array);
        self::assertIsArray($array['default']);

        self::assertArrayHasKey('type', $array['default']);
        self::assertEquals(get_class($databasePlatform), $array['default']['type']);

        self::assertArrayHasKey('database', $array['default']);
        self::assertNull($array['default']['database']);

        self::assertArrayHasKey('driver', $array['default']);
        self::assertEquals(get_class($driver), $array['default']['driver']);
    }
}
