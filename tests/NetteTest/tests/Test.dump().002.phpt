<?php

/**
 * Test: TestHelpers::dump() extra characters
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

require __DIR__ . '/initialize.php';



$arr['a"]\'b\\x10\\b'] = 'a"]\'b';

$arr['žluťoučký'] = 'žluťoučký';

$arr["\x01\xF5"] = "\x01\xF5";

$arr['bin'] = implode('', range("\x00", "\xFF"));


TestHelpers::dump( $arr );

TestHelpers::dump( (object) $arr );

TestHelpers::dump( "a\r\n\tb" );



__halt_compiler() ?>
