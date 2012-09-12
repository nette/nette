<?php

/**
 * Test: Nette\DI\Container NestedAccessor.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI\Container,
	Nette\DI\NestedAccessor;



require __DIR__ . '/../bootstrap.php';



class Service
{
}

$one = new Service;


$container = new Container(array(
	'nested' => array(
		'item' => 1,
	),
));
$container->addService('one', $one);
$container->nested = new NestedAccessor($container, 'nested');

Assert::false( isset($container->nested->one) );

$container->addService('nested.one', $one);

Assert::true( isset($container->nested->one) );
Assert::same( $one, $container->nested->one );

Assert::same( 1, $container->nested->parameters['item'] );
