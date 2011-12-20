<?php

/**
 * Test: Nette\Config\Configurator: services by Class.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
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

Assert::true( $container->one instanceof Lorem );
Assert::true( $container->two instanceof Ipsum );
