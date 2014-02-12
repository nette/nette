<?php

/**
 * Test: Nette\DI\ContainerBuilder and generated factories with arguments.
 *
 * @author     David Matejka
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Foo
{

	public $value;

	public function __construct($value)
	{
		$this->value = $value;
	}
}

interface FooFactory
{

	/** @return Foo */
	public function create();
}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('fooFactory')
		->setImplement('FooFactory')
		->setArguments(array('bar'));

// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::type('FooFactory', $container->getService('fooFactory'));
Assert::type('Foo', $foo = $container->getService('fooFactory')->create());
Assert::same('bar', $foo->value);
