<?php

/**
 * Test: Nette\DI\ContainerBuilder and injection into properties.
 *
 * @author     David Grudl
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


interface IFoo
{
}

class Foo implements IFoo
{
}

class Test1
{
	/** @inject @var stdClass */
	public $varA;

	/** @var stdClass @inject */
	public $varB;

}

class Test2 extends Test1
{
	/** @var stdClass @inject */
	public $varC;

	/** @var IFoo @inject */
	public $varD;

}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('stdClass');
$builder->addDefinition('two')
	->setClass('Foo');


// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

$test = new Test2;
$container->callInjects($test);
Assert::type( 'stdClass', $test->varA );
Assert::type( 'stdClass', $test->varB );
Assert::type( 'stdClass', $test->varC );
Assert::type( 'Foo', $test->varD );
