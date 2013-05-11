<?php

/**
 * Test: Nette\DI\ContainerBuilder and injection into private properties.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



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
	protected $varA;

	/** @var stdClass @inject */
	protected $varB;
}

class Test2 extends Test1
{
	/** @var stdClass @inject */
	private $varC;

	/** @var stdClass @inject */
	protected $varD;
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
foreach ((array) $test as $property) {
	Assert::true( $property instanceof stdClass );
}
