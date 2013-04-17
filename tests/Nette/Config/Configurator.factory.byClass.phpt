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

Assert::true( $container->getService('one') instanceof Lorem );
Assert::true( $container->getService('two') instanceof Ipsum );
Assert::true( $container->getService('three') instanceof Lorem );
Assert::same( $container->getService('one'), $container->getService('three') );
Assert::true( $container->getService('four') instanceof Lorem );
Assert::same( $container->getService('one'), $container->getService('four') );
