<?php

/**
 * Test: Nette\DI\Container errors usage.
 */

use Nette\DI\Container;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$service = new stdClass;
$container = new Container;

Assert::exception(function () use ($container, $service) {
	$container->addService(NULL, $service);
}, 'Nette\InvalidArgumentException', 'Service name must be a non-empty string, NULL given.');

Assert::exception(function () use ($container) {
	$container->addService('one', NULL);
}, 'Nette\InvalidArgumentException', 'Service must be a object, NULL given.');

Assert::exception(function () use ($container) {
	$container->getService('one');
}, 'Nette\DI\MissingServiceException', "Service 'one' not found.");

Assert::exception(function () use ($container, $service) {
	$container->addService('one', $service);
	$container->addService('one', $service);
}, 'Nette\InvalidStateException', "Service 'one' already exists.");
