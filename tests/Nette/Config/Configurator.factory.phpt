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
		Notes::add(__METHOD__ . ' ' . $arg);
		return new Lorem;
	}
}


class Lorem
{
}

class Ipsum
{
	function __construct($arg)
	{
		Notes::add(__METHOD__ . ' ' . $arg);
	}
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.factory.neon', Configurator::NONE)
	->createContainer();

Assert::true( $container->getService('one') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 1',
), Notes::fetch());

Assert::true( $container->getService('two') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 1',
), Notes::fetch());

Assert::true( $container->getService('three') instanceof Lorem );
Assert::same(array(
	'Factory::createLorem 1',
), Notes::fetch());

Assert::true( $container->getService('four') instanceof Lorem );
Assert::same(array(
	'Factory::createLorem 1',
), Notes::fetch());

Assert::true( $container->getService('five') instanceof Lorem );
Assert::same(array(
	'Factory::createLorem 1',
), Notes::fetch());

Assert::true( $container->getService('six') instanceof Lorem );
Assert::same(array(
	'Factory::createLorem 1',
), Notes::fetch());

Assert::true( $container->getService('seven') instanceof Lorem );

Assert::true( $container->getService('eight') instanceof Lorem );

Assert::true( $container->getService('alias') instanceof Ipsum );
Assert::same( $container->getService('one'), $container->getService('alias') );

Assert::true( $container->getByType('stdClass') instanceof stdClass );
