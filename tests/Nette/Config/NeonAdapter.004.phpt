<?php

/**
 * Test: Nette\Config\NeonAdapter section.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../bootstrap.php';



try {
	$config = Config::fromFile('config3.neon');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception( 'Nette\InvalidStateException', "Missing parent section 'scalar' in 'config3.neon'.", $e );
}
