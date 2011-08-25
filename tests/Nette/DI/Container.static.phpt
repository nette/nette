<?php

/**
 * Test: Nette\DI\Container static usage.
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



class MyContainer extends Container
{

	protected function createServiceOne()
	{
		return NULL;
	}

	protected function createServiceTwo()
	{
	}

}



$container = new MyContainer;

$container->addService('one', (object) NULL);

Assert::true( $container->hasService('one') );
Assert::false( $container->hasService('undefined') );

Assert::true( $container->getService('one') instanceof stdClass );
Assert::same( $container->getService('one'), $container->getService('one') ); // shared


// bad method
Assert::throws(function() use ($container) {
	$container->getService('two');
}, 'Nette\UnexpectedValueException', "Unable to create service 'two', value returned by factory 'createServiceTwo' is not object.");
