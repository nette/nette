<?php

/**
 * Test: Nette\DI\Container magic properties.
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



class Service
{
}

$one = new Service;
$two = new Service;


$container = new Container;
$container->one = $one;
$container->addService('two', 'Service');

Assert::true( isset($container->one) );
Assert::true( isset($container->two) );
Assert::false( isset($container->undefined) );

Assert::same( $one, $container->one );
Assert::true( $container->two instanceof Service );
Assert::same( $container->two, $container->getService('two') );
