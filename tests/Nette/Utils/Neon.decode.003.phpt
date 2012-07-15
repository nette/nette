<?php

/**
 * Test: Nette\Utils\Neon::decode errors.
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\Neon;



require __DIR__ . '/../bootstrap.php';



Assert::throws(function() {
	Neon::decode("Hello\nWorld");
}, 'Nette\Utils\NeonException', "Unexpected 'World' on line 2, column 1." );


Assert::throws(function() {
	Neon::decode("- Dave,\n- Rimmer,\n- Kryten,\n");
}, 'Nette\Utils\NeonException', "Unexpected ',' on line 1, column 6." );


Assert::throws(function() {
	Neon::decode("- first: Dave\n last: Lister\n gender: male\n");
}, 'Nette\Utils\NeonException', "Unexpected ':' on line 1, column 7." );


Assert::throws(function() {
	Neon::decode('item [a, b]');
}, 'Nette\Utils\NeonException', "Unexpected ',' on line 1, column 7." );


Assert::throws(function() {
	Neon::decode('{,}');
}, 'Nette\Utils\NeonException', "Unexpected ',' on line 1, column 1." );


Assert::throws(function() {
	Neon::decode('{a, ,}');
}, 'Nette\Utils\NeonException', "Unexpected ',' on line 1, column 4." );
