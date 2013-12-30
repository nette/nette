<?php

/**
 * Test: Nette\DI\Compiler and ExtensionsExtension.
 *
 * @author     David Grudl
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class FooExtension extends DI\CompilerExtension
{
	function loadConfiguration()
	{
		$this->getContainerBuilder()->parameters['foo'] = 'hello';
	}
}


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$compiler->addExtension('extensions', new Nette\DI\Extensions\ExtensionsExtension);
$code = $compiler->compile($loader->load('files/compiler.extension.extensions.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::same( 'hello', $container->parameters['foo'] );
