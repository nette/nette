<?php

/**
 * Test: Nette\DI\Compiler: arguments in config.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Lorem
{
	const DOLOR_SIT = 10;

	public $args;

	public $var = 123;

	function __construct()
	{
		$this->args[] = func_get_args();
	}

	function method()
	{
		$this->args[]  = func_get_args();
	}

	function add($a, $b)
	{
		return $a + $b;
	}

}

define('MY_CONSTANT_TEST', "one");


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$code = $compiler->compile($loader->load('files/compiler.arguments.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;
$container->parameters = array('something');


$lorem = $container->getService('lorem');

// constants
Assert::same( array('one', Lorem::DOLOR_SIT, 'MY_FAILING_CONSTANT_TEST'), $lorem->args[0] );
Assert::error(function () use ($container) {
	$container->getService('dolor');
}, E_NOTICE, "Use of undefined constant MY_FAILING_CONSTANT_TEST - assumed 'MY_FAILING_CONSTANT_TEST'");

// services
Assert::same( array($lorem, $lorem, $container), $lorem->args[1] );

// statements
Assert::same( array(3, 'HELLO'), $lorem->args[2] );

// non-statements
Assert::same( array(array('Lorem', 'method'), 'Lorem::add', 'Lorem::add'), $lorem->args[3] );

// special
Assert::same( array(FALSE), $lorem->args[4] );

// service variables
Assert::same( array($lorem->var, $lorem->var, $container->parameters), $lorem->args[5] );

// service constant
Assert::same( array(Lorem::DOLOR_SIT, Lorem::DOLOR_SIT, Container::TAGS), $lorem->args[6] );
