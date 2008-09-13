<?php

/*use Nette::Environment;*/


// Step 1: Load Nette Framework
// this allows Nette to load classes automatically so that
// you don't have to litter your code with 'require' statements
// require_once LIBS_DIR . '/Nette/loader.php';
require_once dirname(__FILE__) . '/../../../Nette/loader.php';



// Step 2: Configure and setup application environment
// 2a) enable Nette::Debug for better exception and error visualisation
Debug::enable();

// 2b) check if directory /app/temp is writable
if (!is_writable(Environment::getVariable('tempDir'))) {
	throw new Exception("Make directory '" . Environment::getVariable('tempDir') . "' writable!");
}



// Step 3: Get the front controller
$application = Environment::getApplication();



// Step 4: Run the application!
$application->run();
