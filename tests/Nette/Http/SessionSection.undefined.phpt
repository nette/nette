<?php

/**
 * Test: Nette\Http\SessionSection undefined property.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Session;


require __DIR__ . '/../bootstrap.php';


ini_set('session.save_path', TEMP_DIR);


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$session = $container->getService('session');

$namespace = $session->getSection('one');
Assert::false( isset($namespace->undefined) );
Assert::null( $namespace->undefined ); // Getting value of non-existent key
Assert::same( '', http_build_query($namespace->getIterator()) );
