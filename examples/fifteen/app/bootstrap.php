<?php

/*use Nette::Environment;*/

/**
 * Load Nette
 */
require_once dirname(__FILE__) . '/../../../Nette/loader.php';



/**
 * Setup Nette::Debug
 */
/*Nette::*/Debug::enable();



/**
 * Configure application
 */
if (!is_writable(Environment::getVariable('tempDir'))) {
	throw new Exception("Make directory '" . Environment::getVariable('tempDir') . "' writable!");
}



/**
 * Run!
 */
$application = Environment::getApplication();
$application->run();
