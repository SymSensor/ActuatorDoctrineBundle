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

namespace SymSensor\ActuatorDoctrineBundle\Service\Health\Indicator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use SymSensor\ActuatorBundle\Service\Health\Health;
use SymSensor\ActuatorBundle\Service\Health\HealthInterface;
use SymSensor\ActuatorBundle\Service\Health\HealthStack;
use SymSensor\ActuatorBundle\Service\Health\Indicator\HealthIndicator;

class Doctrine implements HealthIndicator
{
    /**
     * @var array<array{'connection': Connection, 'sql': ?string}>
     */
    private array $checks;

    /**
     * @param array<array{'connection': Connection, 'sql': ?string}> $checks
     */
    public function __construct(array $checks = [])
    {
        $this->checks = $checks;
    }

    public function name(): string
    {
        return 'doctrine';
    }

    public function health(): HealthInterface
    {
        $healthList = [];
        foreach ($this->checks as $name => $check) {
            $connection = $check['connection'];
            $checkSql = $check['sql'];
            try {
                if (!$connection instanceof Connection) {
                    throw new \InvalidArgumentException(\sprintf('"connection" should be instance of %s, but got %s', Connection::class, $connection::class));
                }

                $detailCheck = [];
                if (null !== $checkSql) {
                    $connection->executeQuery($checkSql);
                    $detailCheck['check_sql'] = $checkSql;
                } else {
                    $connection->connect();
                }

                $healthList[$name] = Health::up($detailCheck);
            } catch (Exception $e) {
                $healthList[$name] = Health::down($e->getMessage());
            } catch (\InvalidArgumentException $e) {
                $healthList[$name] = Health::unknown($e->getMessage());
            }
        }

        if (0 === \count($healthList)) {
            return Health::unknown('No database connection checked');
        }
        if (1 === \count($healthList)) {
            return \current($healthList);
        }

        return new HealthStack($healthList);
    }
}
