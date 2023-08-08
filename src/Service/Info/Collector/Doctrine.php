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

namespace SymSensor\ActuatorDoctrineBundle\Service\Info\Collector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use SymSensor\ActuatorBundle\Service\Info\Collector\Collector;
use SymSensor\ActuatorBundle\Service\Info\Info;

class Doctrine implements Collector
{
    /**
     * @var array<string, Connection>
     */
    private array $connections;

    /**
     * @param array<string, Connection> $connections
     */
    public function __construct(array $connections)
    {
        $this->connections = $connections;
    }

    public function collect(): Info
    {
        $connectionInfo = [];
        foreach ($this->connections as $name => $connection) {
            if (!$connection instanceof Connection) {
                $connectionInfo[$name] = [
                    'type' => 'Unknown',
                    'database' => 'Unknown',
                    'driver' => 'Unknown',
                ];

                continue;
            }

            try {
                $platform = $connection->getDatabasePlatform();
                if (null === $platform) { // @phpstan-ignore-line
                    $type = 'Unknown';
                } else {
                    $type = \trim((new \ReflectionClass($connection->getDatabasePlatform()))->getShortName(), 'Platform');
                }
            } catch (Exception $e) {
                $type = 'Unknown';
            }

            try {
                $database = $connection->getDatabase();
            } catch (Exception $e) {
                $database = 'Unknown';
            }

            $connectionInfo[$name] = [
            'type' => $type,
            'database' => $database,
            'driver' => \get_class($connection->getDriver()),
            ];
        }

        return new Info('doctrine', $connectionInfo);
    }
}
