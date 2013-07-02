<?php

/**
 * Test: Nette\Config\Configurator and user extension.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\Config\Configurator;


require __DIR__ . '/../bootstrap.php';


class DatabaseExtension extends Nette\DI\CompilerExtension
{
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$configurator->onCompile[] = function(Configurator $configurator, Nette\DI\Compiler $compiler) {
	$compiler->addExtension('database', new DatabaseExtension);
};
$container = $configurator->addConfig('files/compiler.extension.neon')
	->createContainer();

Assert::type( 'stdClass', $container->getService('database.foo') );
