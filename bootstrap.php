<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();

//Doctrine
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = array(
    __DIR__ . "/src"
);

$isDevMode = false;
// the connection configuration
$dbParams = array(
    'driver' => 'pdo_mysql',
    'host' => '127.0.0.1',
    'user' => 'root',
    'password' => '',
    'dbname' => 'doctrine',
);

$configDoctrine = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);

$entityManager = EntityManager::create($dbParams, $configDoctrine);
