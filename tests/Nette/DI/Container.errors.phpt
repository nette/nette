<?php

/**
 * Test: Nette\DI\Container errors usage.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



$service = (object) NULL;
$container = new Container;

try {
	$container->addService(NULL, $service);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidArgumentException', 'Service name must be a non-empty string, NULL given.', $e );
}

try {
	$container->addService('one', NULL);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidArgumentException', 'Invalid callback.', $e );
}

try {
	$container->getService('one');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', "Service 'one' not found.", $e );
}

try {
	$container->addService('one', $service);
	$container->addService('one', $service);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\DI\AmbiguousServiceException', "Service named 'one' has already been registered.", $e );
}

try {
	$container->freeze();
	$container->addService('two', $service);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', 'Cannot modify a frozen object Nette\DI\Container.', $e );
}
