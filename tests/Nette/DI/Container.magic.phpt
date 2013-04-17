<?php

/**
 * Test: Nette\DI\Container magic properties.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



class Service
{
}

$one = new Service;
$two = new Service;


$container = new Container(array('nette' => array('accessors' => TRUE)));
$container->one = $one;
@$container->addService('two', 'Service'); // deprecated

Assert::true( isset($container->one) );
Assert::true( isset($container->two) );
Assert::false( isset($container->undefined) );

Assert::same( $one, $container->one );
Assert::true( $container->two instanceof Service );
Assert::same( $container->two, $container->getService('two') );

Assert::true( isset($container->one) );
Assert::true( isset($container->two) );
Assert::false( isset($container->undefined) );
