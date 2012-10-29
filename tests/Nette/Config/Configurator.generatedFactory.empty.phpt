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

}


class Lorem
{

}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$configurator->addConfig('files/config.generatedFactory.empty.neon', Configurator::NONE);

Assert::throws(function () use ($configurator) {
	$configurator->createContainer();
}, 'Nette\InvalidStateException', "Method ILoremFactory::create() in factory of 'lorem' must be defined.");
