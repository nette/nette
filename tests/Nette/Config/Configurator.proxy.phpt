<?php

/**
 * Test: Nette\Config\Configurator proxy.
 *
 * @author     Jakub Vrana
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



$_SERVER['SERVER_ADDR'] = '192.0.32.10';
Assert::true( Configurator::detectProductionMode(), 'Is production mode?' );



$_SERVER['SERVER_ADDR'] = '127.0.0.1';
Assert::false( Configurator::detectProductionMode(), 'Is production mode without proxy?' );



$_SERVER['HTTP_X_FORWARDED_FOR'] = '192.0.32.10';
Assert::true( Configurator::detectProductionMode(), 'Is production mode with proxy?' );



$_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
Assert::false( Configurator::detectProductionMode(), 'Is production mode with local proxy?' );
