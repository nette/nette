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

$container = new Container;
$container->one = $one;

Assert::true( isset($container->one) );
Assert::same( $one, $container->one );

Assert::false( isset($container->undefined) );
