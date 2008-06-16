<?php


/**
 * Load Nette
 */
require_once dirname(__FILE__) . '/../../../Nette/loader.php';


/**
 * Configure application
 */
Environment::loadConfig();


/**
 * Prepare & setup
 */
Debug::enable(E_ALL | E_STRICT);


$application = Environment::getApplication();
$router = $application->getRouter();
$router[] = new Route('index.php', array(
	'module' => 'front',
	'presenter' => 'default',
), Route::ONE_WAY);

$router[] = new Route('<presenter #d>/<view>/<id>', array(
	'presenter' => 'default',
	'view' => 'default',
	'id' => NULL,
));



/**
 * Run!
 */
$application->run();
