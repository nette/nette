<?php

/**
 * Test: Nette\Configurator and production mode.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\Configurator;


require __DIR__ . '/../bootstrap.php';


$configurator = new Configurator;

Assert::false( $configurator->isDebugMode() );

$configurator->setDebugMode(TRUE);
Assert::true( $configurator->isDebugMode() );
Assert::false( @$configurator->isProductionMode() );

$configurator->setDebugMode(FALSE);
Assert::false( $configurator->isDebugMode() );
Assert::true( @$configurator->isProductionMode() );

$configurator->setDebugMode(php_uname('n'));
Assert::true( $configurator->isDebugMode() );

$configurator->setDebugMode(array(php_uname('n')));
Assert::true( $configurator->isDebugMode() );

$_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
Assert::false( $configurator->detectDebugMode() );
Assert::true( $configurator::detectDebugMode(php_uname('n')) );
