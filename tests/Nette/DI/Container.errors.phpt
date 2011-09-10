<?php

/**
 * Test: Nette\DI\Container errors usage.
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



$service = (object) NULL;
$container = new Container;

Assert::throws(function() use ($container, $service) {
	$container->addService(NULL, $service);
}, 'Nette\InvalidArgumentException', 'Service name must be a non-empty string, NULL given.');

Assert::throws(function() use ($container) {
	$container->addService('one', NULL);
}, 'Nette\InvalidArgumentException', 'Invalid callback.');

Assert::throws(function() use ($container) {
	$container->getService('one');
}, 'Nette\DI\MissingServiceException', "Service 'one' not found.");

Assert::throws(function() use ($container, $service) {
	$container->addService('one', $service);
	$container->addService('one', $service);
}, 'Nette\InvalidStateException', "Service 'one' has already been registered.");

Assert::throws(function() use ($container, $service) {
	$container->freeze();
	$container->addService('two', $service);
}, 'Nette\InvalidStateException', 'Cannot modify a frozen object Nette\DI\Container.');
