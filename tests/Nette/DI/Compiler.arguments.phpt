<?php

/**
 * Test: Nette\DI\Compiler: arguments in config.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;


require __DIR__ . '/../bootstrap.php';


class Lorem
{
	const DOLOR_SIT = 10;

	public $args;

	function __construct()
	{
		$this->args[] = func_get_args();
	}
}

define('MY_CONSTANT_TEST', "one");


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$code = $compiler->compile($loader->load('files/compiler.arguments.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


$lorem = $container->getService('lorem');

// constants
Assert::same( array('one', Lorem::DOLOR_SIT, 'MY_FAILING_CONSTANT_TEST'), $lorem->args[0] );
Assert::error(function () use ($container) {
	$container->getService('dolor');
}, E_NOTICE, "Use of undefined constant MY_FAILING_CONSTANT_TEST - assumed 'MY_FAILING_CONSTANT_TEST'");
