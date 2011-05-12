<?php

/**
 * Test: Nette\Config\IniAdapter errors.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../bootstrap.php';



try {
	$config = Config::fromFile('config.missing-section.ini');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception( 'Nette\InvalidStateException', "Missing parent section [scalar] in file '%a%'.", $e );
}


try {
	$config = Config::fromFile('config.scalar1.ini');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception( 'Nette\InvalidStateException', "Invalid section [scalar.set] in file '%a%'.", $e );
}


try {
	$config = Config::fromFile('config.scalar2.ini');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception( 'Nette\InvalidStateException', "Invalid key 'date.timezone' in section [set] in file '%a%'.", $e );
}
