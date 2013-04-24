<?php

/**
 * Test: Nette\Config\Compiler: services setup.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config;



require __DIR__ . '/../bootstrap.php';



class LoremIpsumMacros extends Nette\Latte\Macros\MacroSet
{

	public static function install(Nette\Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('lorem', 'lorem');
		Notes::add(get_class($me));
	}

}



class IpsumLoremMacros extends Nette\Latte\Macros\MacroSet
{

	public static function install(Nette\Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('ipsum', 'ipsum');
		Notes::add(get_class($me));
	}

}




$loader = new Config\Loader;
$config = $loader->load('files/compiler.extension.nette.neon');
$config['parameters']['debugMode'] = FALSE;
$config['parameters']['productionMode'] = FALSE;
$config['parameters']['tempDir'] = '';

$compiler = new Config\Compiler;
$compiler->addExtension('nette', new Nette\Config\Extensions\NetteExtension);
$code = $compiler->compile($config, 'Container', 'Nette\DI\Container');


file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::true( $container->createNette__latte() instanceof Nette\Latte\Engine );

Assert::same(array(
	'LoremIpsumMacros',
	'IpsumLoremMacros',
), Notes::fetch());
