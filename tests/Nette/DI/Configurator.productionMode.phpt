<?php

/**
 * Test: Nette\Configurator and production mode.
 *
 * @author     David Grudl
 */

use Nette\Configurator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$configurator = new Configurator;

Assert::false( $configurator->isDebugMode() );

$configurator->setDebugMode(TRUE);
Assert::true( $configurator->isDebugMode() );

$configurator->setDebugMode(FALSE);
Assert::false( $configurator->isDebugMode() );

$configurator->setDebugMode(php_uname('n'));
Assert::true( $configurator->isDebugMode() );

$configurator->setDebugMode(array(php_uname('n')));
Assert::true( $configurator->isDebugMode() );

$_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
Assert::false( $configurator->detectDebugMode() );
Assert::true( $configurator::detectDebugMode(php_uname('n')) );
