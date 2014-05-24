<?php

/**
 * Test: Nette\DI\Compiler and user extension.
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class DatabaseExtension extends Nette\DI\CompilerExtension
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

	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		Notes::add(__METHOD__);
	}
}


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$extension = new DatabaseExtension;
$compiler->addExtension('database', $extension);
$code = $compiler->compile($loader->load('files/compiler.extension.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::same(array(
	'DatabaseExtension::loadConfiguration',
	'DatabaseExtension::beforeCompile',
	'DatabaseExtension::afterCompile',
), Notes::fetch());

Assert::type( 'stdClass', $container->getService('database.foo') );
Assert::same( $container->getService('database.foo'), $container->getService('alias') );


Assert::same( 'database.', $extension->prefix('') );
Assert::same( 'database.member', $extension->prefix('member') );
Assert::same( '@database.member', $extension->prefix('@member') );


Assert::same( array('foo' => 'hello'), $extension->getConfig() );
Assert::same( array('foo' => 'hello'), $extension->getConfig(array('foo' => 'bar')) );
Assert::same( array('foo2' => 'hello', 'foo' => 'hello'), $extension->getConfig(array('foo2' => '%bar%')) );
