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



/**
 * Setup router
 */
$application = Environment::getApplication();

$router = $application->getRouter();

$router[] = new Route('index.php', array(
	'module' => 'Front',
	'presenter' => 'Default',
), Route::ONE_WAY);

$router[] = new Route('<presenter>/<view>/<id>', array(
	'presenter' => 'Front:Default',
	'view' => 'default',
	'id' => NULL,
));



/**
 * Run!
 */
$application->run();
