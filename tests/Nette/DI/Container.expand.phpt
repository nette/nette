<?php

/**
 * Test: Nette\DI\Container expand.
 */

use Nette\DI\Container;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$container = new Container(array(
	'appDir' => '/myApp',
	'dirs' => array('cache' => '/temp'),
));

Assert::same('/myApp/test', $container->expand('%appDir%/test'));
Assert::same('/temp/test', $container->expand('%dirs.cache%/test'));
Assert::same(array('cache' => '/temp'), $container->expand('%dirs%'));

Assert::exception(function () use ($container) {
	$container->expand('%bar%');
}, 'Nette\InvalidArgumentException', "Missing parameter 'bar'.");

Assert::exception(function () use ($container) {
	$container->expand('%foo.bar%');
}, 'Nette\InvalidArgumentException', "Missing parameter 'foo.bar'.");

Assert::exception(function () use ($container) {
	$container->parameters['bar'] = array();
	$container->expand('foo%bar%');
}, 'Nette\InvalidArgumentException', "Unable to concatenate non-scalar parameter 'bar' into 'foo%bar%'.");
