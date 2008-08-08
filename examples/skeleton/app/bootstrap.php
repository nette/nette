<?php

/*use Nette::Environment;*/
/*use Nette::Application::Route;*/

/**
 * Load Nette
 */
define('LIBS_DIR', APP_DIR . '/../libs');

// this should be removed
if (!is_dir(LIBS_DIR . '/Nette')) {
	die("Extract Nette Framework to library directory '" . realpath(LIBS_DIR) . "'.");
}

require_once LIBS_DIR . '/Nette/loader.php';



/**
 * Setup Nette::Debug
 */
/*Nette::*/Debug::enable();



/**
 * Configure application
 */
$config = Environment::loadConfig();



/**
 * Enable RobotLoader
 */
$loader = new /*Nette::Loaders::*/RobotLoader();
$loader->addDirectory(explode(';', $config->scanDirs));
$loader->register();



/**
 * Setup router
 */
$application = Environment::getApplication();
$application->errorPresenter = 'Error';

$router = $application->getRouter();

$router[] = new Route('index.php', array(
	'presenter' => 'Homepage',
	'view' => 'default',
), Route::ONE_WAY);

$router[] = new Route('<presenter>/<view>/<id>', array(
	'presenter' => 'Homepage',
	'view' => 'default',
	'id' => NULL,
));



/**
 * Run!
 */
$application->run();
