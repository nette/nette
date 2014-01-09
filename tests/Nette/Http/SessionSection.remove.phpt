<?php

/**
 * Test: Nette\Http\SessionSection remove.
 *
 * @author     David Grudl
 */

use Nette\Http\Session,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$session = $container->getService('session');

$namespace = $session->getSection('three');
$namespace->a = 'apple';
$namespace->p = 'papaya';
$namespace['c'] = 'cherry';

$namespace = $session->getSection('three');
Assert::same( 'a=apple&p=papaya&c=cherry', http_build_query($namespace->getIterator()) );


// removing
$namespace->remove();
Assert::same( '', http_build_query($namespace->getIterator()) );
