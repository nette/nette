<?php

/**
 * Test: Nette\DI\Config\Adapters\NeonAdapter errors.
 *
 * @author     David Grudl
 */

use Nette\DI\Config,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function() {
	$config = new Config\Loader;
	$config->load('files/neonAdapter.scalar.neon');
}, 'Nette\InvalidStateException', "Duplicated key 'scalar'.");
