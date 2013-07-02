<?php

/**
 * Test: Nette\DI\Compiler: constants in config.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;


require __DIR__ . '/../bootstrap.php';


class Lorem
{
	const DOLOR_SIT = 10;

	public $arg;

	function __construct($arg)
	{
		$this->arg = $arg;
	}
}

define('MY_CONSTANT_TEST', "one");


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$compiler->addExtension('constants', new Nette\DI\Extensions\ConstantsExtension);
$code = $compiler->compile($loader->load('files/compiler.extension.constants.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::same( "one", $container->getService('ipsum')->arg );
Assert::same( Lorem::DOLOR_SIT, $container->getService('sit')->arg );
Assert::same( "MY_FAILING_CONSTANT_TEST", $container->getService('consectetur')->arg );

Assert::error(function () use ($container) {
	$container->getService('amet')->arg;
}, E_NOTICE, "Use of undefined constant MY_FAILING_CONSTANT_TEST - assumed 'MY_FAILING_CONSTANT_TEST'");
