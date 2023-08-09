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

namespace SymSensor\ActuatorDoctrineBundle\Tests\Service\Health\Indicator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use PHPUnit\Framework\TestCase;
use SymSensor\ActuatorBundle\Service\Health\HealthStack;
use SymSensor\ActuatorBundle\Service\Health\HealthState;
use SymSensor\ActuatorDoctrineBundle\Service\Health\Indicator\Doctrine;

class DoctrineTest extends TestCase
{
    /**
     * @test
     */
    public function nameOfIndicator(): void
    {
        $databaseHealthIndicator = new Doctrine([]);

        self::assertEquals('doctrine', $databaseHealthIndicator->name());
    }

    /**
     * @test
     */
    public function willThrowIfConnectionIsNotConnection(): void
    {
        $connection = new \stdClass();
        $checks = [
            [
                'connection' => $connection,
                'sql' => null,
            ],
        ];

        $healthIndicator = new Doctrine($checks); // @phpstan-ignore-line

        $health = $healthIndicator->health();

        self::assertEquals(HealthState::UNKNOWN, $health->getStatus());
    }

    /**
     * @test
     */
    public function willConnectToConnectionIfNoSql(): void
    {
        $connection = self::createMock(Connection::class);

        $connection->expects(self::once())
            ->method('connect');

        $checks = [
            [
                'connection' => $connection,
                'sql' => null,
            ],
        ];
        $healthIndicator = new Doctrine($checks);

        $health = $healthIndicator->health();

        self::assertTrue($health->isUp());
    }

    /**
     * @test
     */
    public function healthIsNotUpIfConnectionFails(): void
    {
        $connection = self::createMock(Connection::class);

        $connection->expects(self::once())
            ->method('connect')
            ->willThrowException(self::createMock(DriverException::class))
        ;

        $checks = [
            [
                'connection' => $connection,
                'sql' => null,
            ],
        ];
        $healthIndicator = new Doctrine($checks);

        $health = $healthIndicator->health();

        self::assertFalse($health->isUp());
    }

    /**
     * @test
     */
    public function willCheckConnectionWithSql(): void
    {
        $connection = self::createMock(Connection::class);

        $connection->expects(self::once())
            ->method('executeQuery')
        ;

        $checks = [
            [
                'connection' => $connection,
                'sql' => 'SELECT 1=1',
            ],
        ];
        $healthIndicator = new Doctrine($checks);

        $health = $healthIndicator->health();

        self::assertTrue($health->isUp());
    }

    /**
     * @test
     */
    public function healthIsNotUpIfSqlFails(): void
    {
        $connection = self::createMock(Connection::class);

        $connection->expects(self::once())
            ->method('executeQuery')
            ->willThrowException(self::createMock(DriverException::class))
        ;

        $checks = [
            [
                'connection' => $connection,
                'sql' => 'SELECT 1=1',
            ],
        ];
        $healthIndicator = new Doctrine($checks);

        $health = $healthIndicator->health();

        self::assertFalse($health->isUp());
    }

    /**
     * @test
     */
    public function healthChecksEveryConnection(): void
    {
        $connection1 = self::createMock(Connection::class);
        $connection2 = self::createMock(Connection::class);

        $connection1->expects(self::once())
            ->method('executeQuery')
        ;

        $connection2->expects(self::once())
            ->method('connect')
        ;

        $checks = [
            'conn1' => [
                'connection' => $connection1,
                'sql' => 'SELECT 1=1',
            ],
            'conn2' => [
                'connection' => $connection2,
                'sql' => null,
            ],
        ];
        $healthIndicator = new Doctrine($checks);

        $health = $healthIndicator->health();

        self::assertInstanceOf(HealthStack::class, $health);
        self::assertCount(3, $health->jsonSerialize());
        self::assertArrayHasKey('conn1', $health->jsonSerialize());
        self::assertArrayHasKey('conn2', $health->jsonSerialize());
    }
}
