<?php

/**
 * Test: Nette\Config\Configurator: services factories.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator;


require __DIR__ . '/../bootstrap.php';


class Factory
{
	static function createLorem($arg)
	{
		return new Lorem(__METHOD__ . ' ' . $arg);
	}
}


class Lorem
{
	function __construct($arg = NULL)
	{
		$this->arg = $arg;
	}

}

class Ipsum
{
	function __construct($arg)
	{
		$this->arg = $arg;
	}
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.factory.neon', Configurator::NONE)
	->createContainer();

Assert::type( 'Ipsum', $container->getService('one') );
Assert::same( 1, $container->getService('one')->arg );

Assert::type( 'Ipsum', $container->getService('two') );
Assert::same( 1, $container->getService('two')->arg );

Assert::type( 'Lorem', $container->getService('three') );
Assert::same( 'Factory::createLorem 1', $container->getService('three')->arg );

Assert::type( 'Lorem', $container->getService('four') );
Assert::same( 'Factory::createLorem 1', $container->getService('four')->arg );

Assert::type( 'Lorem', $container->getService('five') );
Assert::same( 'Factory::createLorem 1', $container->getService('five')->arg );

Assert::type( 'Lorem', $container->getService('six') );
Assert::same( 'Factory::createLorem 1', $container->getService('six')->arg );

Assert::type( 'Lorem', $container->getService('seven') );

Assert::type( 'Lorem', $container->getService('eight') );

Assert::type( 'Ipsum', $container->getService('alias') );
Assert::same( $container->getService('one'), $container->getService('alias') );

Assert::type( 'stdClass', $container->getByType('stdClass') );
