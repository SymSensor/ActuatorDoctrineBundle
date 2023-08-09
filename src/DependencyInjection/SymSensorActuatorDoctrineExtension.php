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

final class SymSensorActuatorDoctrineExtension extends Extension
{
    /**
     * @param mixed[] $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->processHealthConfiguration($config, $container);
        $this->processInfoConfiguration($config, $container);
    }

    /**
     * @param mixed[] $config
     */
    private function processHealthConfiguration(array $config, ContainerBuilder $container): void
    {
        if (
            $container->willBeAvailable('doctrine/doctrine-bundle', Connection::class, [])
            && $this->isConfigEnabled($container, $config)
        ) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
            $loader->load('doctrine_health.yaml');

            $definition = $container->getDefinition(\SymSensor\ActuatorDoctrineBundle\Service\Health\Indicator\Doctrine::class);

            if (\is_array($config['connections'])) {
                $constructorArgument = [];
                foreach ($config['connections'] as $name => $connection) {
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
        if (
            $container->willBeAvailable('doctrine/doctrine-bundle', Connection::class, [])
            && $this->isConfigEnabled($container, $config)
        ) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
            $loader->load('doctrine_info.yaml');

            if (isset($config['connections']) && \is_array($config['connections'])) {
                $connectionReferences = [];
                foreach ($config['connections'] as $name => $connectionDefinition) {
                    $connectionReferences[$name] = new Reference($connectionDefinition['service']);
                }
                $definition = $container->getDefinition(\SymSensor\ActuatorDoctrineBundle\Service\Info\Collector\Doctrine::class);
                $definition->replaceArgument(0, $connectionReferences);
            }
        }
    }
}
