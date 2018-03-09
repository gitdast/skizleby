<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
$configurator->setDebugMode(file_exists(__DIR__ . '/config/devmode'));
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	//->addDirectory(__DIR__. '/../vendor/dibi')
	->addDirectory(APP_DIR)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
