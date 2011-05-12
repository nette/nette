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

Assert::same( '/myApp/test', $container->expand('%appDir%/test') );

try {
	$container->expand('%bar%');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidArgumentException', "Missing parameter 'bar'.", $e );
}

try {
	$container->params['bar'] = array();
	$container->expand('%bar%');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', "Parameter 'bar' is not scalar.", $e );
}
