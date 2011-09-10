<?php

/**
 * Test: Nette\Config\NeonAdapter errors.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../bootstrap.php';



Assert::throws(function() {
	$config = Config::fromFile('config.scalar1.neon');
}, 'Nette\InvalidStateException', "Missing parent section 'scalar' in file '%a%'.");
