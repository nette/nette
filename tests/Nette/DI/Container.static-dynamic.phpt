<?php

/**
 * Test: Nette\DI\Container static & dynamic usage.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



class MyContainer extends Container
{

	protected function createServiceOne()
	{
		return NULL;
	}

}



$container = new MyContainer;

Assert::true( $container->hasService('one') );

$container->addService('one', (object) NULL);

Assert::true( $container->hasService('one') );

Assert::true( $container->getService('one') instanceof stdClass );
Assert::same( $container->getService('one'), $container->getService('one') ); // shared
