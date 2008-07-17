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
/*Nette::*/Debug::enable(E_ALL | E_STRICT);



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
	'presenter' => 'Default',
	'view' => 'default',
), Route::ONE_WAY);

$router[] = new Route('<presenter>/<view>/<id>', array(
	'presenter' => 'Default',
	'view' => 'default',
	'id' => NULL,
));



/**
 * Run!
 */
$application->run();
