<?php

/*use Nette\Environment;*/
/*use Nette\Application\Route;*/



// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
// require_once LIBS_DIR . '/Nette/loader.php';
require_once dirname(__FILE__) . '/../../../Nette/loader.php';



// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
Debug::enable(E_ALL | E_STRICT);

// 2b) load configuration from config.ini file
Environment::loadConfig();

// 2c) check if directory /app/temp is writable
if (!is_writable(Environment::getVariable('tempDir'))) {
	throw new Exception("Make directory '" . Environment::getVariable('tempDir') . "' writable!");
}

// 2d) enable RobotLoader - this allows load all classes automatically
$loader = new /*Nette\Loaders\*/RobotLoader();
$loader->addDirectory(APP_DIR);
$loader->addDirectory(LIBS_DIR);
$loader->register();



// Step 3: Configure application
// 3a) get and setup a front controller
$application = Environment::getApplication();

// 3b) establish database connection
$application->onStartup[] = 'Albums::initialize';


// Step 4: Setup application router
$router = $application->getRouter();

$router[] = new Route('index.php', array(
	'presenter' => 'Dashboard',
	'action' => 'default',
), Route::ONE_WAY);

$router[] = new Route('<presenter>/<action>/<id>', array(
	'presenter' => 'Dashboard',
	'action' => 'default',
	'id' => NULL,
));



// Step 5: Run the application!
$application->run();
