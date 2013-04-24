<?php

/**
 * Test: Nette\Config\Compiler and ExtensionsExtension.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config;



require __DIR__ . '/../bootstrap.php';



class FooExtension extends Config\CompilerExtension
{
	function loadConfiguration()
	{
		$this->getContainerBuilder()->parameters['foo'] = 'hello';
	}
}




$loader = new Config\Loader;
$compiler = new Config\Compiler;
$compiler->addExtension('extensions', new Nette\Config\Extensions\ExtensionsExtension);
$code = $compiler->compile($loader->load('files/compiler.extension.extensions.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::same( 'hello', $container->parameters['foo'] );
