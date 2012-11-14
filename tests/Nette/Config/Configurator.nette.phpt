<?php

/**
 * Test: Nette\Config\Configurator: services setup.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator;



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


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.nette.neon')
	->createContainer();

Assert::true( $container->createNette__latte() instanceof Nette\Latte\Engine );

Assert::same(array(
	'LoremIpsumMacros',
	'IpsumLoremMacros',
), Notes::fetch());
