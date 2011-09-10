<?php

/**
 * Test: Nette\DI\Container by type.
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



class Service implements Countable
{
	function count(){}
}

$one = new Service;
$two = new Service;


$container = new Container;
$container->addService('one', $one);
$container->addService('two', function($container){
	return (object) NULL;
}, 'stdClass');


Assert::same( $one, $container->getServiceByType('Service') );
Assert::true( $container->getServiceByType('stdClass') instanceof stdClass );

Assert::true( $container->checkServiceType('one', 'Service') );
Assert::true( $container->checkServiceType('one', 'service') );
Assert::false( $container->checkServiceType('one', 'stdClass') );
Assert::false( $container->checkServiceType('one', '') );
Assert::true( $container->checkServiceType('two', 'stdClass') );
Assert::false( $container->checkServiceType('none', 'stdClass') );

// hint priority
$container->addService('three', function($container){
	return new Service;
}, 'Countable');
Assert::same( $one, $container->getServiceByType('Service') );
$container->getService('three');
Assert::same( $one, $container->getServiceByType('Service') );


// errors
Assert::throws(function() use ($container) {
	$container->getServiceByType('unknown');
}, 'Nette\DI\MissingServiceException', "Service matching 'unknown' type not found.");

Assert::throws(function() use ($container) {
	$container->addService('double', 'Service');
	$container->getServiceByType('Service');
}, 'Nette\DI\AmbiguousServiceException', "Found more than one service ('one', 'double') matching 'Service' type.");

try {
	$container->addService('invalid', function($container){
		return new Service;
	}, 'ArrayAccess');
	$container->getService('invalid');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\UnexpectedValueException', "Unable to create service 'invalid', value returned by factory '%a%' is not 'ArrayAccess' type.", $e );
}
