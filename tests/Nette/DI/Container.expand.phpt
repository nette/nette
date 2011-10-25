<?php

/**
 * Test: Nette\DI\Container expand.
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



$container = new Container;
$container->parameters['appDir'] = '/myApp';
$container->parameters['dirs']['cache'] = '/temp';

Assert::same( '/myApp/test', $container->expand('%appDir%/test') );
Assert::same( '/temp/test', $container->expand('%dirs.cache%/test') );
Assert::same( array('cache' => '/temp'), $container->expand('%dirs%') );

Assert::throws(function() use ($container) {
	$container->expand('%bar%');
}, 'Nette\InvalidArgumentException', "Missing item 'bar'.");

Assert::throws(function() use ($container) {
	$container->parameters['bar'] = array();
	$container->expand('foo%bar%');
}, 'Nette\InvalidArgumentException', "Unable to concatenate non-scalar parameter 'bar' into 'foo%bar%'.");
