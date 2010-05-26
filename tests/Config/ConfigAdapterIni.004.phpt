<?php

/**
 * Test: Nette\Config\ConfigAdapterIni section.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../NetteTest/initialize.php';



try {
	output("Example 3");
	$config = Config::fromFile('config3.ini');
	dump( $config );
} catch (Exception $e) {
	dump( $e );
}


try {
	output("Example 4");
	$config = Config::fromFile('config4.ini');
	dump( $config );
} catch (Exception $e) {
	dump( $e );
}


try {
	output("Example 5");
	$config = Config::fromFile('config5.ini');
	dump( $config );
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Example 3

Exception InvalidStateException: Missing parent section [scalar] in 'config3.ini'.

Example 4

Exception InvalidStateException: Invalid section [scalar.set] in 'config4.ini'.

Example 5

Exception InvalidStateException: Invalid key 'date.timezone' in section [set] in 'config5.ini'.
