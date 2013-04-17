<?php

/**
 * Test: Nette\Config\Configurator and user extension.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator,
	Nette\Config\Compiler,
	Nette\DI\ContainerBuilder;



require __DIR__ . '/../bootstrap.php';



class DatabaseExtension extends Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		Assert::same( array('foo' => 'hello'), $this->config );
		Notes::add(__METHOD__);
	}

	public function beforeCompile()
	{
		Notes::add(__METHOD__);
	}

	public function afterCompile(Nette\Utils\PhpGenerator\ClassType $class)
	{
		Notes::add(__METHOD__);
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
), Notes::fetch());

Assert::true( $container->getService('database.foo') instanceof stdClass );
Assert::same( $container->getService('database.foo'), $container->getService('alias') );


Assert::same( 'database.', $extension->prefix('') );
Assert::same( 'database.member', $extension->prefix('member') );
Assert::same( '@database.member', $extension->prefix('@member') );


Assert::same( array('foo' => 'hello'), $extension->getConfig() );
Assert::same( array('foo' => 'hello'), $extension->getConfig(array('foo' => 'bar')) );
Assert::same( array('foo' => '%bar%'), $extension->getConfig(NULL, FALSE) );
