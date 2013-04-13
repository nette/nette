<?php

/**
 * Test: Nette\DI\Config\Adapters\NeonAdapter errors.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI\Config;



require __DIR__ . '/../bootstrap.php';



Assert::exception(function() {
	$config = new Config\Loader;
	$config->load('files/config.scalar1.neon');
}, 'Nette\InvalidStateException', "Duplicated key 'scalar'.");
