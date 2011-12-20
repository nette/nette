<?php

/**
 * Test: Nette\Config\Configurator and dots in 'php' section in INI.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



$configurator = new Configurator;
$configurator->setCacheDirectory(TEMP_DIR);

date_default_timezone_set('America/Los_Angeles');
set_include_path('');

$configurator->addConfig('files/config.php.ini')
	->createContainer();

Assert::same( 'Europe/Prague', date_default_timezone_get() );
Assert::same( 'libs', get_include_path() );