<?php

/*use Nette::Environment;*/
/*use Nette::Application::Route;*/

/**
 * Load Nette
 */
require_once dirname(__FILE__) . '/../../../Nette/loader.php';



/**
 * Setup Nette::Debug
 */
/*Nette::*/Debug::enable();



/**
 * Configure application
 */
Environment::loadConfig();

if (!is_writable(Environment::getVariable('tempDir'))) {
	throw new Exception("Make directory '" . Environment::getVariable('tempDir') . "' writable!");
}



/**
 * Setup database connection
 */
require_once 'dibi.compact.php';
dibi::connect(Environment::getConfig('database'));



/**
 * Setup router
 */
$application = Environment::getApplication();

$router = $application->getRouter();

$router[] = new Route('index.php', array(
	'presenter' => 'Dashboard',
	'view' => 'default',
), Route::ONE_WAY);

$router[] = new Route('<presenter>/<view>/<id>', array(
	'presenter' => 'Dashboard',
	'view' => 'default',
	'id' => NULL,
));



/**
 * Run!
 */
$application->run();
