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
	 * @return \stdClass
	 */
	function create();

	function foo();

	function bar();
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$configurator->addConfig('files/config.generatedFactory.methods.neon', Configurator::NONE);

Assert::throws(function () use ($configurator) {
	$configurator->createContainer();
}, 'Nette\InvalidStateException', "The interface ILoremFactory can contain only create() method. Methods foo, bar are extra.");
