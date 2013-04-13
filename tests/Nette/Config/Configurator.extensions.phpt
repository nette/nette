<?php

/**
 * Test: Nette\DI\Configurator and ExtensionsExtension.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI\Configurator;



require __DIR__ . '/../bootstrap.php';



class FooExtension extends Nette\DI\CompilerExtension
{
	function loadConfiguration()
	{
		$this->getContainerBuilder()->parameters['foo'] = 'hello';
	}
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.extensions.neon')
	->createContainer();

Assert::same( 'hello', $container->parameters['foo'] );
