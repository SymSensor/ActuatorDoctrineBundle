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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sym_sensor_actuator_doctrine');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode // @phpstan-ignore-line
            ->canBeDisabled()
            ->children()
                ->arrayNode('connections')
                    ->useAttributeAsKey('name')
                    ->defaultValue(['default' => ['service' => 'doctrine.dbal.default_connection', 'check_sql' => 'SELECT 1']])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('service')->isRequired()->end()
                            ->scalarNode('check_sql')->defaultValue('SELECT 1')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
