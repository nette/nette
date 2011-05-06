<?php

/**
 * Test: Nette\DI\Container static usage.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



class MyContainer extends Container
{

	protected function createServiceOne()
	{
		return (object) NULL;
	}

	protected function createServiceTwo()
	{
	}

}



$container = new MyContainer;

try {
	$container->addService('one', (object) NULL);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\DI\AmbiguousServiceException', "Service named 'one' has already been registered.", $e );
}

Assert::true( $container->hasService('one') );
Assert::false( $container->hasService('undefined') );

Assert::true( $container->getService('one') instanceof stdClass );
Assert::true( $container->getService('one') === $container->getService('one') ); // shared


// bad method
try {
	$container->getService('two');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\DI\AmbiguousServiceException', "Cannot instantiate service 'two', value returned by 'createServiceTwo' is not object.", $e );
}
