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

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\Version\MigrationPlanCalculator;
use Doctrine\Migrations\Version\MigrationStatusCalculator;
use SymSensor\ActuatorBundle\Service\Info\Collector\Collector;
use SymSensor\ActuatorBundle\Service\Info\Info;

class DoctrineMigrations implements Collector
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

    public function collect(): Info
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

        return new Info('doctrine-migrations', $dataGroup);
    }
}
