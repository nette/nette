<?php

/**
 * Test: Nette\DI\Container cloning.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



class Service
{
}

$service = new Service;


$container = new Container;
$container->addService('one', $service);
$container->addService('two', 'Service');
$container->addService('three', function($container){
	return new Service;
});

$dolly = clone $container;

Assert::true( $dolly->getService('one') === $container->getService('one') );
Assert::true( $dolly->getService('two') === $container->getService('two') );
Assert::true( $dolly->getService('three') === $container->getService('three') );


$container->addService('oneX', $service);
$container->addService('twoX', 'Service');
$container->addService('threeX', function($container){
	return new Service;
});

Assert::false( $dolly->hasService('oneX') );
Assert::false( $dolly->hasService('twoX') );
Assert::false( $dolly->hasService('threeX') );
