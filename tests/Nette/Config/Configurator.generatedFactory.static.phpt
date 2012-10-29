<?php

/**
 * Test: Nette\Config\Configurator: generated services factories.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



interface ILoremFactory
{

	/**
	 * @return Lorem
	 */
	static function create();
}

class Lorem
{

}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$configurator->addConfig('files/config.generatedFactory.static.neon', Configurator::NONE);

Assert::throws(function () use ($configurator) {
	$configurator->createContainer();
}, 'Nette\InvalidStateException', "Method ILoremFactory::create() in factory of 'lorem' must not be static.");
