# ActuatorDoctrineBundle

<img src="https://github.com/SymSensor/ActuatorDoctrineBundle/blob/main/docs/logo.png?raw=true" align="right" width="250"/>

ActuatorDoctrineBundle extends [ActuatorBundle](https://github.com/SymSensor/ActuatorBundle) by providing health indicator and information collector for doctrine.

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require symsensor/actuator-doctrine-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require symsensor/actuator-doctrine-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    SymSensor\ActuatorBundle\SymSensorActuatorDoctrineBundle::class => ['all' => true],
];
```


## Configuration

The Bundle can be configured with a configuration file named `config/packages/sym_sensor_actuator.yaml`. Following snippet shows the default value for all configurations:

```yaml
sym_sensor_actuator_doctrine:
  connections:
    default:
      service: doctrine.dbal.default_connection
      check_sql: SELECT 1
```

Following table outlines the configuration:

| key                                                       | default                    | description                                                                                                                                                                                                                                             |
| --------------------------------------------------------- | -------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| sym_sensor_actuator_doctrine.connections                  | Array                      | Contains a list of names, where each represents an connection to e database. The name itself can be chosen at will                                                                                                                                      |
| sym_sensor_actuator_doctrine.connections.`name`.enabled   | true                       | If the connection associated with this name should monitored                                                                                                                                                                                            |
| sym_sensor_actuator_doctrine.connections.`name`.service   | 'Doctrine\DBAL\Connection' | The service name inside the dependency injection container. You can lookup your connection name with `bin/console debug:container`                                                                                                                      |
| sym_sensor_actuator_doctrine.connections.`name`.check_sql | 'Select 1'                 | The SQL which will be executed to determine if the database is up. The response will be ignored, it only matters if the sql can be executed without error. If you set this to `~` it will only check if a connection to the database can be established |


## License

ActuatorBundle is released under the MIT Licence. See the bundled LICENSE file for details.

## Author

Originally developed by [Arkadiusz Kondas](https://twitter.com/ArkadiuszKondas)
