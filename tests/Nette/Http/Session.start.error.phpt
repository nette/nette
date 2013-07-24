<?php

/**
 * Test: Nette\Http\Session error in session_start.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Session,
	Nette\Http\SessionSection;


require __DIR__ . '/../bootstrap.php';


ini_set('session.gc_probability', 0); // ensure to GC not run
ini_set('session.save_path', ';;;');


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$session = $container->getService('session');

Assert::exception(function() use ($session) {
	$session->start();
}, 'Nette\InvalidStateException', "session_start(): session_start(): open(%A%) failed: %a%");
