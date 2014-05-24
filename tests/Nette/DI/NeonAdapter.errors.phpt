<?php

/**
 * Test: Nette\DI\Config\Adapters\NeonAdapter errors.
 */

use Nette\DI\Config,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function() {
	$config = new Config\Loader;
	$config->load('files/neonAdapter.scalar.neon');
}, 'Nette\InvalidStateException', "Duplicated key 'scalar'.");
