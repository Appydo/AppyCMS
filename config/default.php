<?php
return array(
    'db' => array(
        'driver'         => 'pdo_sqlite',
        'dsn'            => 'sqlite:' . getcwd() . '/data/appydo',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
        'username' => 'YOUR USERNAME HERE',
        'password' => 'YOUR PASSWORD HERE',
    ),
    'install' => 0,
    'version' => 0.3,
    'zone' => 'Europe/Paris',
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
);
