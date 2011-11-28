<?php

/**
 * Test: Nette\Config\Configurator and loadConfig.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



date_default_timezone_set('America/Los_Angeles');

$configurator = new Configurator;
$configurator->setCacheDirectory(TEMP_DIR);
$container = $configurator->loadConfig('files/config.basic.neon', 'production');

Assert::same( 'hello world', $container->parameters['foo'] );
Assert::same( 'hello', $container->parameters['bar'] );
Assert::same( 'hello world', constant('BAR') );
Assert::same( 'Europe/Prague', date_default_timezone_get() );

Assert::equal( array(
	'dsn' => 'sqlite2::memory:',
	'user' => 'dbuser',
	'password' => 'secret',
), $container->parameters['database'] );
