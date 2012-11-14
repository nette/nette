<?php

/**
 * Test: Overriding class of service definition defined in CompilerExtension.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator,
	Nette\Config\Compiler,
	Nette\DI\ContainerBuilder;



require __DIR__ . '/../bootstrap.php';



class Factory
{
	static function createLorem($arg = NULL)
	{
		return new Lorem($arg);
	}
}


class IpsumFactory
{
	static function create($arg = NULL)
	{
		return new Ipsum($arg);
	}
}


class Lorem
{
	function __construct($arg = NULL)
	{
		Notes::add(__METHOD__ . ' ' . $arg);
	}
}

class Ipsum
{
	function __construct($arg = NULL)
	{
		Notes::add(__METHOD__ . ' ' . $arg);
	}
}


class FooExtension extends Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$container->parameters['class'] = 'Lorem';

		$container->addDefinition('one1')
			->setClass('%class%', array(1));
		$container->addDefinition('one2')
			->setClass('%class%', array(1));
		$container->addDefinition('one3')
			->setClass('%class%', array(1));
		$container->addDefinition('one4')
			->setClass('%class%', array(1));
		$container->addDefinition('one5')
			->setClass('%class%', array(1));
		$container->addDefinition('one6')
			->setClass('%class%', array(1));
		$container->addDefinition('one7')
			->setClass('%class%', array(1));

		$container->addDefinition('two1')
			->setClass('Lorem')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('two2')
			->setClass('Lorem')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('two3')
			->setClass('Lorem')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('two4')
			->setClass('Lorem')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('two5')
			->setClass('Lorem')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('two6')
			->setClass('Lorem')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('two7')
			->setClass('Lorem')
			->setFactory('Factory::createLorem', array(1));

		$container->addDefinition('three1')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('three2')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('three3')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('three4')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('three5')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('three6')
			->setFactory('Factory::createLorem', array(1));
		$container->addDefinition('three7')
			->setFactory('Factory::createLorem', array(1));
	}

}



$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$configurator->onCompile[] = function(Configurator $configurator, Compiler $compiler){
	$compiler->addExtension('database', new FooExtension);
};
$container = $configurator->addConfig(__DIR__ . '/files/config.extensionOverride.neon', Configurator::NONE)
	->createContainer();

Assert::true( $container->one1 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->one2 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->one3 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->one4 instanceof Lorem );
Assert::same(array(
	'Lorem::__construct 2',
), Notes::fetch());

Assert::true( $container->one5 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->one6 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->one7 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());


Assert::true( $container->two1 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->two2 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->two3 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->two4 instanceof Lorem );
Assert::same(array(
	'Lorem::__construct 2',
), Notes::fetch());

Assert::true( $container->two5 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->two6 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->two7 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());


Assert::true( $container->three1 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->three2 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->three3 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->three4 instanceof Lorem );
Assert::same(array(
	'Lorem::__construct 2',
), Notes::fetch());

Assert::true( $container->three5 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->three6 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->three7 instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());
