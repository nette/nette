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
$container->params['appDir'] = '/myApp';
$container->params['dirs']['cache'] = '/temp';

Assert::same( '/myApp/test', $container->expand('%appDir%/test') );
Assert::same( '/temp/test', $container->expand('%dirs.cache%/test') );

try {
	$container->expand('%bar%');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidArgumentException', "Missing item 'bar'.", $e );
}

try {
	$container->params['bar'] = array();
	$container->expand('%bar%');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', "Parameter 'bar' is not scalar.", $e );
}
