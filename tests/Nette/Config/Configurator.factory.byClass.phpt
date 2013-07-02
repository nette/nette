<?php

/**
 * Test: Nette\Config\Configurator: services by Class.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator;


require __DIR__ . '/../bootstrap.php';


class Lorem
{
	function __construct(Ipsum $arg)
	{
	}
}

class Ipsum
{
	static function foo()
	{
	}
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.factory.byClass.neon', Configurator::NONE)
	->createContainer();

Assert::type( 'Lorem', $container->getService('one') );
Assert::type( 'Ipsum', $container->getService('two') );
Assert::type( 'Lorem', $container->getService('three') );
Assert::same( $container->getService('one'), $container->getService('three') );
Assert::type( 'Lorem', $container->getService('four') );
Assert::same( $container->getService('one'), $container->getService('four') );
