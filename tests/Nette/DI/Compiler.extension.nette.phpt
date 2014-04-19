<?php

/**
 * Test: Nette\DI\Compiler: services setup.
 *
 * @author     David Grudl
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class LoremIpsumMacros extends Latte\Macros\MacroSet
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('lorem', 'lorem');
		Notes::add(get_class($me));
	}

}


class IpsumLoremMacros extends Latte\Macros\MacroSet
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('ipsum', 'ipsum');
		Notes::add(get_class($me));
	}

}


$loader = new DI\Config\Loader;
$config = $loader->load('files/compiler.extension.nette.neon');
$config['parameters']['debugMode'] = FALSE;
$config['parameters']['productionMode'] = TRUE;
$config['parameters']['tempDir'] = '';

$compiler = new DI\Compiler;
$compiler->addExtension('nette', new Nette\Bridges\Framework\NetteExtension);
$code = $compiler->compile($config, 'Container', 'Nette\DI\Container');


file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::type( 'Nette\Bridges\Framework\ILatteFactory', $container->createService('nette.latteFactory') );
