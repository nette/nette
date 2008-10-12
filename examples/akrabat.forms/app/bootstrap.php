<?php

/*use Nette::Environment;*/
/*use Nette::Application::Route;*/


// Step 1: Load Nette Framework
// this allows Nette to load classes automatically so that
// you don't have to litter your code with 'require' statements
// require_once LIBS_DIR . '/Nette/loader.php';
require_once dirname(__FILE__) . '/../../../Nette/loader.php';



// Step 2: Configure and setup application environment
// 2a) enable Nette::Debug for better exception and error visualisation
Debug::enable(E_ALL | E_STRICT);

// 2b) load configuration from config.ini file
Environment::loadConfig();

// 2c) check if directory /app/temp is writable
if (!is_writable(Environment::getVariable('tempDir'))) {
	throw new Exception("Make directory '" . Environment::getVariable('tempDir') . "' writable!");
}

// 2d) establish database connection
require_once LIBS_DIR . '/dibi.compact.php';
dibi::connect(Environment::getConfig('database'));



// Step 3: Get the front controller
$application = Environment::getApplication();



// Step 4: Setup application routes
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



// Step 5: Run the application!
$application->run();
