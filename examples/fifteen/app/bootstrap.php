<?php

use Nette\Debug,
	Nette\Environment,
	Nette\Loaders\RobotLoader;



// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
// require LIBS_DIR . '/Nette/loader.php';
require __DIR__ . '/../../../Nette/loader.php';



// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
Debug::enable();

// 2b) enable RobotLoader - this allows load all classes automatically
$loader = new RobotLoader();
$loader->addDirectory(APP_DIR);
$loader->register();



// Step 3: Configure application
$application = Environment::getApplication();



// Step 4: Run the application!
$application->run();
