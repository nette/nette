<?php

/**
 * My Application bootstrap file.
 *
 * @copyright  Copyright (c) 2009 John Doe
 * @package    MyApplication
 * @version    $Id$
 */


/*use Nette\Environment;*/
/*use Nette\Application\Route;*/



// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require_once LIBS_DIR . '/Nette/loader.php';
//require_once dirname(__FILE__) . '/../../../Nette/loader.php';



// Step 2: Configure and setup application environment
// 2a) enable Nette\Debug for better exception and error visualisation
Debug::enable();

// 2b) load configuration from config.ini file
Environment::loadConfig();

// 2c) enable RobotLoader - this allows load all classes automatically
$loader = new /*Nette\Loaders\*/RobotLoader();
$loader->addDirectory(APP_DIR);
$loader->addDirectory(LIBS_DIR);
$loader->register();

// 2d) setup sessions
$session = Environment::getSession();
$session->setSavePath(APP_DIR . '/sessions/');



// Step 3: Get the front controller
$application = Environment::getApplication();
// 2b) setup front controller
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;



// Step 4: Setup application routes
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



// Step 5: Run the application!
$application->run();
