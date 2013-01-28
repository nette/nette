<?php

/**
 * Test: Nette\Config\Configurator and circular services.
 *
 * @author     David Grudl
 * @package    Nette\Config
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



Assert::exception(function() {
	$configurator = new Configurator;
	$configurator->setTempDirectory(TEMP_DIR);
	$configurator->addConfig('files/config.services.circular.neon')
	->createContainer();
}, 'Nette\InvalidStateException', 'Circular reference detected for services: ipsum, lorem.');
