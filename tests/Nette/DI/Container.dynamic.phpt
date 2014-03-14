<?php

/**
 * Test: Nette\DI\Container dynamic usage.
 *
 * @author     David Grudl
 */

use Nette\DI\Container,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Service
{
	static function create()
	{
		return new static;
	}
}


$container = new Container;

test(function() use ($container) {
	$one = new Service;
	$two = new Service;
	$container->addService('one', $one);
	$container->addService('two', $two);

	Assert::true( $container->hasService('one') );
	Assert::true( $container->isCreated('one') );
	Assert::true( $container->hasService('two') );
	Assert::false( $container->hasService('undefined') );

	Assert::same( $one, $container->getService('one') );
	Assert::same( $two, $container->getService('two') );
});
