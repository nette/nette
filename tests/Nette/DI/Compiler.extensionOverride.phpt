<?php

/**
 * Test: Overriding class of service definition defined in CompilerExtension.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



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


class FooExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$container->addDefinition('one1')
			->setClass('Lorem', array(1));
		$container->addDefinition('one2')
			->setClass('Lorem', array(1));
		$container->addDefinition('one3')
			->setClass('Lorem', array(1));
		$container->addDefinition('one4')
			->setClass('Lorem', array(1));
		$container->addDefinition('one5')
			->setClass('Lorem', array(1));
		$container->addDefinition('one6')
			->setClass('Lorem', array(1));
		$container->addDefinition('one7')
			->setClass('Lorem', array(1));

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





$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$extension = new FooExtension;
$compiler->addExtension('database', $extension);
$code = $compiler->compile($loader->load('files/compiler.extensionOverride.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::type( 'Ipsum', $container->getService('one1') );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('one2') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('one3') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::type( 'Lorem', $container->getService('one4') );
Assert::same(array(
	'Lorem::__construct 2',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('one5') );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('one6') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('one7') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());


Assert::type( 'Ipsum', $container->getService('two1') );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('two2') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('two3') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::type( 'Lorem', $container->getService('two4') );
Assert::same(array(
	'Lorem::__construct 2',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('two5') );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('two6') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('two7') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());


Assert::type( 'Ipsum', $container->getService('three1') );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('three2') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('three3') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::type( 'Lorem', $container->getService('three4') );
Assert::same(array(
	'Lorem::__construct 2',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('three5') );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('three6') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::type( 'Ipsum', $container->getService('three7') );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());
