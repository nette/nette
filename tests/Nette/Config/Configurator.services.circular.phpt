<?php

/**
 * Test: Nette\Config\Configurator and circular services.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



class Lorem
{
	function __construct(Ipsum $foo)
	{
	}
}


class Ipsum
{
	function __construct(Lorem $foo)
	{
	}
}



Assert::throws(function() {
	$configurator = new Configurator;
	$configurator->setCacheDirectory(TEMP_DIR);
	$configurator->loadConfig('files/config.services.circular.neon', FALSE);
}, 'Nette\InvalidStateException', 'Circular reference detected for services: ipsum, lorem.');
