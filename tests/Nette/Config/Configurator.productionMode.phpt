<?php

/**
 * Test: Nette\Config\Configurator and production mode.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator = new Configurator;

Assert::true( $configurator->isProductionMode() );

$configurator->setProductionMode(FALSE);
Assert::false( $configurator->isProductionMode() );

$configurator->setProductionMode();
Assert::true( $configurator->isProductionMode() );

$configurator->setProductionMode(php_uname('n'));
Assert::false( $configurator->isProductionMode() );

$configurator->setProductionMode(array(php_uname('n')));
Assert::false( $configurator->isProductionMode() );

$_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
Assert::false( $configurator::detectDebugMode() );
Assert::true( $configurator::detectDebugMode(php_uname('n')) );
