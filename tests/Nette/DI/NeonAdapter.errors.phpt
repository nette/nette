<?php

/**
 * Test: Nette\DI\Config\Adapters\NeonAdapter errors.
 *
 * @author     David Grudl
 * @package    Nette\DI\Config
 */

use Nette\DI\Config;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function() {
	$config = new Config\Loader;
	$config->load('files/neonAdapter.scalar.neon');
}, 'Nette\InvalidStateException', "Duplicated key 'scalar'.");
