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
