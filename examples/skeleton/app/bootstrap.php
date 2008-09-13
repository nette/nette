<?php

/*use Nette::Environment;*/
/*use Nette::Application::Route;*/


// Step 1: Load Nette Framework
// this allows Nette to load classes automatically so that
// you don't have to litter your code with 'require' statements
require_once LIBS_DIR . '/Nette/loader.php';
//require_once dirname(__FILE__) . '/../../../Nette/loader.php';



// Step 2: Configure and setup application environment
// 2a) enable Nette::Debug for better exception and error visualisation
Debug::enable();

// 2b) load configuration from config.ini file
$config = Environment::loadConfig();

// 2c) enable RobotLoader
$loader = new /*Nette::Loaders::*/RobotLoader();
$loader->addDirectory(explode(';', $config->scanDirs));
$loader->autoRebuild = FALSE;
$loader->register();



// Step 3: Get the front controller
$application = Environment::getApplication();
// 2b) setup front controller
$application->errorPresenter = 'Error';
$application->catchExceptions = TRUE;



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
