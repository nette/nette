<?php

/**
 * Test: Nette\Http\Session error in session_start.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Session,
	Nette\Http\SessionSection;



require __DIR__ . '/../bootstrap.php';


ini_set('session.save_path', ';;;');



$container = id(new Nette\Config\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();

Assert::throws(function() use ($container) {
	$session = $container->session->start();
}, 'Nette\InvalidStateException', "session_start(): session_start(): open(%A%) failed: %a%");
