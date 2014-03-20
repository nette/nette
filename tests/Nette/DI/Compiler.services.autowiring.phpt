<?php

/**
 * Test: Nette\DI\Compiler and autowiring.
 *
 * @author     David Grudl
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Factory
{
	/** @return Model  auto-wiring using annotation */
	static function createModel()
	{
		return new Model;
	}
}


class Model
{
	/** autowiring using parameters */
	function test(Lorem $arg)
	{
		Notes::add(__METHOD__);
	}
}


class Lorem
{
	/** autowiring using parameters */
	static function test(Ipsum $arg)
	{
		Notes::add(__METHOD__);
	}
}

class Ipsum
{}


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$code = $compiler->compile($loader->load('files/compiler.services.autowiring.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::type( 'Model', $container->getService('model') );

Assert::same(array(
	'Model::test',
	'Model::test',
	'Model::test',
	'Lorem::test',
	'Lorem::test',
), Notes::fetch());
