<?php

/**
 * Test: Nette\Environment proxy.
 *
 * @author     Jakub Vrana
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\DI\Configurator;



require __DIR__ . '/../bootstrap.php';
$configurator = new Configurator;



$_SERVER["SERVER_ADDR"] = "192.0.32.10";
Assert::true( $configurator->detect('production'), 'Is production mode?' );



$_SERVER["SERVER_ADDR"] = "127.0.0.1";
Assert::false( $configurator->detect('production'), 'Is production mode without proxy?' );



$_SERVER["HTTP_X_FORWARDED_FOR"] = "192.0.32.10";
Assert::true( $configurator->detect('production'), 'Is production mode with proxy?' );



$_SERVER["HTTP_X_FORWARDED_FOR"] = "127.0.0.1";
Assert::false( $configurator->detect('production'), 'Is production mode with local proxy?' );
