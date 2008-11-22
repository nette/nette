<?php

/*use Nette\Environment;*/



// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
// require_once LIBS_DIR . '/Nette/loader.php';
require_once dirname(__FILE__) . '/../../../Nette/loader.php';



// Step 2: Configure and setup application environment
// 2a) enable Nette\Debug for better exception and error visualisation
Debug::enable(E_ALL | E_STRICT);

// 2b) check if directory /app/temp is writable
if (!is_writable(Environment::getVariable('tempDir'))) {
	throw new Exception("Make directory '" . Environment::getVariable('tempDir') . "' writable!");
}

// 2c) enable RobotLoader - this allows load all classes automatically
$loader = new /*Nette\Loaders\*/RobotLoader();
$loader->addDirectory(APP_DIR);
$loader->register();



// Step 3: Get the front controller
$application = Environment::getApplication();



// Step 4: Run the application!
$application->run();
