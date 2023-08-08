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

namespace SymSensor\ActuatorDoctrineBundle\DependencyInjection;

use Doctrine\DBAL\Connection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use SymSensor\ActuatorDoctrineBundle\Service\Health\Indicator as HealthIndicator;
use SymSensor\ActuatorDoctrineBundle\Service\Info\Collector as InfoCollector;

final class SymSensorActuatorDoctrineExtension extends Extension
{
    /**
     * @param mixed[] $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->processHealthConfiguration($config['health'], $container);
        $this->processInfoConfiguration($config['info'], $container);
    }

    /**
     * @param mixed[] $config
     */
    private function processHealthConfiguration(array $config, ContainerBuilder $container): void
    {
        $enabled = true;
        if (!$this->isConfigEnabled($container, $config)) {
            $enabled = false;
        }
        $container->setParameter('sym_sensor_actuator_doctrine.health.enabled', $enabled);

        if (
            $container->willBeAvailable('doctrine/doctrine-bundle', Connection::class, [])
            && isset($config['database'])
            && \is_array($config['database'])
            && $this->isConfigEnabled($container, $config['database'])
        ) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
            $loader->load('doctrine_health.yaml');

            $databaseConfig = $config['builtin']['database'];
            $definition = $container->getDefinition(HealthIndicator\Doctrine::class);

            if (\is_array($databaseConfig['connections'])) {
                $constructorArgument = [];
                foreach ($databaseConfig['connections'] as $name => $connection) {
                    if (!\is_array($connection)) {
                        continue;
                    }

                    $constructorArgument[$name] = [
                        'connection' => new Reference($connection['service']),
                        'sql' => $connection['check_sql'],
                    ];
                }

                $definition->replaceArgument(0, $constructorArgument);
            }
        }
    }

    /**
     * @param mixed[] $config
     */
    private function processInfoConfiguration(array $config, ContainerBuilder $container): void
    {
        $enabled = true;
        if (!$this->isConfigEnabled($container, $config)) {
            $enabled = false;
        }
        $container->setParameter('sym_sensor_actuator_doctrine.info.enabled', $enabled);

        if (
            $container->willBeAvailable('doctrine/doctrine-bundle', Connection::class, [])
            && isset($config['database'])
            && \is_array($config['database'])
            && $this->isConfigEnabled($container, $config['builtin']['database'])
        ) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config/extensions'));
            $loader->load('doctrine_info.yaml');

            $databaseConfig = $config['database'];
            if (isset($databaseConfig['connections']) && \is_array($databaseConfig['connections'])) {
                $connectionReferences = [];
                foreach ($databaseConfig['connections'] as $name => $connectionDefinition) {
                    $connectionReferences[$name] = new Reference($connectionDefinition);
                }
                $definition = $container->getDefinition(InfoCollector\Doctrine::class);
                $definition->replaceArgument(0, $connectionReferences);
            }
        }
    }
}
