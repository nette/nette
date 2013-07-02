<?php

/**
 * Test: Nette\DI\Container magic properties (deprecated).
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

$container = new Container(array('container' => array('accessors' => TRUE)));
$container->one = $one;

Assert::true( isset($container->one) );
Assert::same( $one, $container->one );

Assert::false( isset($container->undefined) );


Assert::error(function() {
	$container = new Container;
	$container->one = new Service;
}, E_USER_DEPRECATED, 'Nette\DI\Container::__set() is deprecated; use addService() or enable nette.accessors in configuration.');
