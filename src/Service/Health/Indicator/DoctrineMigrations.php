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

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\Version\MigrationPlanCalculator;
use Doctrine\Migrations\Version\MigrationStatusCalculator;
use SymSensor\ActuatorBundle\Service\Health\Health;
use SymSensor\ActuatorBundle\Service\Health\HealthInterface;
use SymSensor\ActuatorBundle\Service\Health\Indicator\HealthIndicator;

class DoctrineMigrations implements HealthIndicator
{
    private MetadataStorage $metadataStorage;

    private MigrationPlanCalculator $migrationPlanCalculator;

    private MigrationStatusCalculator $statusCalculator;

    public function __construct(private readonly DependencyFactory $dependencyFactory)
    {
        $this->metadataStorage = $this->dependencyFactory->getMetadataStorage();
        $this->migrationPlanCalculator = $this->dependencyFactory->getMigrationPlanCalculator();
        $this->statusCalculator = $this->dependencyFactory->getMigrationStatusCalculator();
    }

    public function name(): string
    {
        return 'doctrine-migrations';
    }

    public function health(): HealthInterface
    {
        $executedMigrations = $this->metadataStorage->getExecutedMigrations();
        $availableMigrations = $this->migrationPlanCalculator->getMigrations();

        $newMigrations = $this->statusCalculator->getNewMigrations();
        $executedUnavailableMigrations = $this->statusCalculator->getExecutedUnavailableMigrations();

        $dataGroup = [
            'executed' => \count($executedMigrations),
            'executed unavailable' => \count($executedUnavailableMigrations),
            'available' => \count($availableMigrations),
            'new' => \count($newMigrations),
        ];

        if (0 !== \count($newMigrations)) {
            return Health::down('Not all migrations were executed', $dataGroup);
        }

        if (0 !== \count($executedUnavailableMigrations)) {
            return Health::unknown('Not all migrations, that were executed, are available anymore', $dataGroup);
        }

        return Health::up($dataGroup);
    }
}
