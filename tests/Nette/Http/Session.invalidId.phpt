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


$_COOKIE['PHPSESSID'] = '#';


$container = id(new Nette\Config\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();

$session = $container->session->start();
