<?php

/**
 * Test: Nette\DI\ContainerBuilder and local autowiring.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



class Foo
{
	public $arg;

	function test(M $arg)
	{
		$this->arg = $arg;
	}
}


class M
{
}


class M1 extends M
{
}


class M2 extends M
{
}



$builder = new DI\ContainerBuilder;
$builder->addDefinition('foo')
	->setClass('Foo');

$builder->addDefinition('m1')
	->setClass('M1')
	->addSetup('@foo::test');

$builder->addDefinition('m2')
	->setClass('M2')
	->addSetup('@foo::test')
	->setAutowired(FALSE);


$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


$foo = $container->getService('foo');
Assert::true( $foo instanceof Foo );
Assert::null( $foo->arg );

Assert::true( $container->getService('m1') instanceof M1 );
Assert::same( $foo->arg, $container->getService('m1') );

Assert::true( $container->getService('m2') instanceof M2 );
Assert::same( $foo->arg, $container->getService('m2') );
