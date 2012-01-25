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

	public function loadConfiguration()
	{
		Assert::equal( array('foo' => 'hello'), $this->config );
		TestHelpers::note(__METHOD__);
	}

	public function beforeCompile()
	{
		TestHelpers::note(__METHOD__);
	}

	public function afterCompile(Nette\Utils\PhpGenerator\ClassType $class)
	{
		TestHelpers::note(__METHOD__);
	}
}



$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$extension = new DatabaseExtension;
$configurator->onCompile[] = function(Configurator $configurator, Compiler $compiler) use ($extension) {
	$compiler->addExtension('database', $extension);
};
$container = $configurator->addConfig('files/config.extension.neon', Configurator::NONE)
	->createContainer();

Assert::same(array(
	'DatabaseExtension::loadConfiguration',
	'DatabaseExtension::beforeCompile',
	'DatabaseExtension::afterCompile',
), TestHelpers::fetchNotes());

Assert::true( $container->database->foo instanceof stdClass );
Assert::same( $container->database->foo, $container->alias );


Assert::same( 'database_', $extension->prefix('') );
Assert::same( 'database_member', $extension->prefix('member') );
Assert::same( '@database_member', $extension->prefix('@member') );


Assert::same( array('foo' => 'hello'), $extension->getConfig() );
Assert::same( array('foo' => 'hello'), $extension->getConfig(array('foo' => 'bar')) );
Assert::same( array('foo' => '%bar%'), $extension->getConfig(NULL, FALSE) );
