<?php

/**
 * Test: Nette\Config\ConfigAdapterIni section.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../initialize.php';



try {
	$config = Config::fromFile('config3.ini');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception( 'InvalidStateException', "Missing parent section [scalar] in 'config3.ini'.", $e );
}


try {
	$config = Config::fromFile('config4.ini');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception( 'InvalidStateException', "Invalid section [scalar.set] in 'config4.ini'.", $e );
}


try {
	$config = Config::fromFile('config5.ini');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception( 'InvalidStateException', "Invalid key 'date.timezone' in section [set] in 'config5.ini'.", $e );
}
