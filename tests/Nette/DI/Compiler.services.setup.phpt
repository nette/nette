<?php

/**
 * Test: Nette\DI\Compiler: services setup.
 *
 * @author     David Grudl
 */

use Nette\DI,
	Tester\Assert;


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
	function test($arg)
	{
		Notes::add(__METHOD__ . ' ' . $arg);
	}
}


class Ipsum
{
	public static $staticTest;

	public $test;

	static function test($arg)
	{
		Notes::add(__METHOD__ . ' ' . $arg);
	}
}


function globtest($arg)
{
	Notes::add(__METHOD__ . ' ' . $arg);
}


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$code = $compiler->compile($loader->load('files/compiler.services.setup.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::same(array(
), Notes::fetch());

Assert::type( 'Lorem', $container->getService('lorem') );

Assert::same(array(
	'Factory::createLorem 1',
	'Lorem::test 2',
	'Lorem::test 3',
	'Lorem::test 4',
	'Ipsum::test 5',
	'Ipsum::test 6',
	'globtest 7',
), Notes::fetch());

Assert::same( 8, $container->getService('lorem')->test );
Assert::same( 9, Ipsum::$staticTest );
Assert::equal( new Lorem, $container->getService('ipsum')->test );
