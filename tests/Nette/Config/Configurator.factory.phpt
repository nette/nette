<?php

/**
 * Test: Nette\Config\Configurator: services factories.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



class Factory
{
	static function createLorem($arg)
	{
		TestHelpers::note(__METHOD__ . ' ' . $arg);
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
		TestHelpers::note(__METHOD__ . ' ' . $arg);
	}
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.factory.neon', Configurator::NONE)
	->createContainer();

Assert::true( $container->one instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 1',
), TestHelpers::fetchNotes());

Assert::true( $container->two instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 1',
), TestHelpers::fetchNotes());

Assert::true( $container->three instanceof Lorem );
Assert::same(array(
	'Factory::createLorem 1',
), TestHelpers::fetchNotes());

Assert::true( $container->four instanceof Lorem );
Assert::same(array(
	'Factory::createLorem 1',
), TestHelpers::fetchNotes());

Assert::true( $container->five instanceof Lorem );
Assert::same(array(
	'Factory::createLorem 1',
), TestHelpers::fetchNotes());

Assert::true( $container->six instanceof Lorem );
Assert::same(array(
	'Factory::createLorem 1',
), TestHelpers::fetchNotes());

Assert::true( $container->seven instanceof Lorem );

Assert::true( $container->eight instanceof Lorem );

Assert::true( $container->alias instanceof Ipsum );
Assert::same( $container->one, $container->alias );

Assert::true( $container->Lorem instanceof Lorem );
