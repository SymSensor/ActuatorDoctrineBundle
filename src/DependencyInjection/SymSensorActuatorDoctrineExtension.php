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

        if (
            $container->willBeAvailable('doctrine/doctrine-bundle', Connection::class, [])
            && $this->isConfigEnabled($container, $config)
        ) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
            $loader->load('services.yaml');

            if (isset($config['connections']) && \is_array($config['connections'])) {
                $constructorArgument = [];
                $infoArgument = [];
                foreach ($config['connections'] as $name => $connection) {
                    if (!\is_array($connection)) {
                        continue;
                    }

                    $constructorArgument[$name] = [
                        'connection' => new Reference($connection['service']),
                        'sql' => $connection['check_sql'],
                    ];
                    $infoArgument[$name] = new Reference($connection['service']);
                }

                $definition = $container->getDefinition(\SymSensor\ActuatorDoctrineBundle\Service\Health\Indicator\Doctrine::class);
                $definition->replaceArgument(0, $constructorArgument);

                $infoDefinition = $container->getDefinition(\SymSensor\ActuatorDoctrineBundle\Service\Info\Collector\Doctrine::class);
                $infoDefinition->replaceArgument(0, $infoArgument);
            }
        }
    }
}
