<?php

/**
 * Test: Nette\DI\Container dynamic usage.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



class Service
{
	static function create()
	{
		return new static;
	}
}

$one = new Service;
$two = new Service;


$container = new Container;
$container->addService('one', $one);
$container->addService('two', $two);

Assert::true( $container->hasService('one') );
Assert::true( $container->isCreated('one') );
Assert::true( $container->hasService('two') );
Assert::false( $container->hasService('undefined') );

Assert::same( $one, $container->getService('one') );
Assert::same( $two, $container->getService('two') );


// class name (deprecated)
Assert::error(function () use ($container) {
	$container->addService('three', 'Service');
}, E_USER_DEPRECATED, 'Passing factories to Nette\DI\Container::addService() is deprecated; pass the object itself.');

Assert::true( $container->hasService('three') );
Assert::type( 'Service', $container->getService('three') );
Assert::same( $container->getService('three'), $container->getService('three') ); // shared


// factory (deprecated)
@$container->addService('factory1', 'Service::create'); // triggers E_USER_DEPRECATED
Assert::true( $container->hasService('factory1') );
Assert::true( $container->isCreated('factory1') );
Assert::type( 'Service', $container->getService('factory1') );


// factory (deprecated)
@$container->addService('factory2', array('Service', 'create'));
Assert::true( $container->hasService('factory2') );
Assert::true( $container->isCreated('factory2') );
Assert::type( 'Service', $container->getService('factory2') );


// closure factory (deprecated)
@$container->addService('factory3', function($container){ // triggers E_USER_DEPRECATED
	Assert::type( 'Nette\DI\Container', $container );
	return new Service;
});
Assert::true( $container->hasService('factory3') );
Assert::true( $container->isCreated('factory3') );
Assert::type( 'Service', $container->getService('factory3') );


// bad factory (deprecated)
Assert::exception(function() use ($container) {
	@$container->addService('five', function($container){}); // triggers E_USER_DEPRECATED
}, 'Nette\InvalidArgumentException', 'Service must be a object, NULL given.');
