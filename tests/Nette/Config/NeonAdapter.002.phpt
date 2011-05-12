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



try {
	$config = Config::fromFile('config.scalar1.neon');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception( 'Nette\InvalidStateException', "Missing parent section 'scalar' in file '%a%'.", $e );
}
