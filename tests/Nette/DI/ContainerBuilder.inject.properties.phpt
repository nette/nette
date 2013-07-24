<?php

/**
 * Test: Nette\DI\ContainerBuilder and inject properties.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;


require __DIR__ . '/../bootstrap.php';


class Test1
{
	/** @inject @var stdClass */
	public $varA;

	/** @var ReflectionClass @inject */
	public $varX;

}

class Test2 extends Test1
{
	/** @var stdClass @inject */
	public $varB;
}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('test')
	->setClass('Test2')
	->addSetup('$varX', 123);

$builder->addDefinition('stdClass')
	->setClass('stdClass');

// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';
$container = new Container;

$test = $container->getService('test');
Assert::type( 'Test1', $test );
Assert::type( 'stdClass', $test->varA );
Assert::type( 'stdClass', $test->varB );
Assert::same( $test->varX, 123 );
