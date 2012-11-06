<?php

/**
 * Test: Nette\Config\Adapters\NeonAdapter errors.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config;



require __DIR__ . '/../bootstrap.php';



Assert::exception(function() {
	$config = new Config\Loader;
	$config->load('files/config.scalar1.neon');
}, 'Nette\InvalidStateException', "Duplicated key 'scalar'.");
