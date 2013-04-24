<?php

/**
 * Test: Overriding class of service definition defined in CompilerExtension.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config;



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





$loader = new Config\Loader;
$compiler = new Config\Compiler;
$extension = new FooExtension;
$compiler->addExtension('database', $extension);
$code = $compiler->compile($loader->load('files/compiler.extensionOverride.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::true( $container->getService('one1') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->getService('one2') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('one3') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('one4') instanceof Lorem );
Assert::same(array(
	'Lorem::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('one5') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->getService('one6') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('one7') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());


Assert::true( $container->getService('two1') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->getService('two2') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('two3') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('two4') instanceof Lorem );
Assert::same(array(
	'Lorem::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('two5') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->getService('two6') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('two7') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());


Assert::true( $container->getService('three1') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->getService('three2') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('three3') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('three4') instanceof Lorem );
Assert::same(array(
	'Lorem::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('three5') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct ',
), Notes::fetch());

Assert::true( $container->getService('three6') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());

Assert::true( $container->getService('three7') instanceof Ipsum );
Assert::same(array(
	'Ipsum::__construct 2',
), Notes::fetch());
