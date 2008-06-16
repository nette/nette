<?php

/**
 * Load Nette
 */
require_once dirname(__FILE__) . '/../../../Nette/loader.php';


/**
 * Prepare & setup
 */
Debug::enable(E_ALL | E_STRICT);


/**
 * Run!
 */
$application = Environment::getApplication();
$application->run();
