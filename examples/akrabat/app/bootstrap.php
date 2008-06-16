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

require_once 'dibi.compact.php';
dibi::connect(Environment::getConfig('database'));


$application = Environment::getApplication();
$router = $application->getRouter();
$router[] = new Route('index.php', array(
	'presenter' => 'default',
), Route::ONE_WAY);

$router[] = new Route('<presenter>/<view>/<id>', array(
	'presenter' => 'default',
	'view' => 'default',
	'id' => NULL,
));



/**
 * Run!
 */
$application->run();
