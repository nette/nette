<?php

/**
 * Test: Nette\Http\SessionSection undefined property.
 *
 * @author     David Grudl
 */

use Nette\Http\Session,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$session = $container->getService('session');

$namespace = $session->getSection('one');
Assert::false( isset($namespace->undefined) );
Assert::null( $namespace->undefined ); // Getting value of non-existent key
Assert::same( '', http_build_query($namespace->getIterator()) );
