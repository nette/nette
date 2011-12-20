<?php

/**
 * Test: Nette\Config\Configurator and user extension.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator,
	Nette\Config\Compiler,
	Nette\DI\ContainerBuilder;



require __DIR__ . '/../bootstrap.php';



class DatabaseExtension extends Nette\Config\CompilerExtension
{

	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		Assert::equal( array('foo' => 'hello'), $config );
		TestHelpers::note(__METHOD__);
	}

	public function beforeCompile(ContainerBuilder $container)
	{
		TestHelpers::note(__METHOD__);
	}

	public function afterCompile(ContainerBuilder $container, Nette\Utils\PhpGenerator\ClassType $class)
	{
		TestHelpers::note(__METHOD__);
	}
}



$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$configurator->onCompile[] = function(Configurator $configurator, Compiler $compiler){
	$compiler->addExtension('database', new DatabaseExtension);
};
$configurator->addConfig('files/config.extension.neon', Configurator::NONE)
	->createContainer();

Assert::same(array(
	'DatabaseExtension::loadConfiguration',
	'DatabaseExtension::beforeCompile',
	'DatabaseExtension::afterCompile',
), TestHelpers::fetchNotes());
