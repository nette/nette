<?php

/**
 * Test: Nette\Diagnostics\Dumper::toText() recursion
 *
 * @author     David Grudl
 */

use Nette\Diagnostics\Dumper,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$arr = array(1, 2, 3);
$arr[] = & $arr;
Assert::match( 'array (4)
   0 => 1
   1 => 2
   2 => 3
   3 => array (4)
   |  0 => 1
   |  1 => 2
   |  2 => 3
   |  3 => array (4) [ RECURSION ]
', Dumper::toText($arr) );


$arr = (object) array('x' => 1, 'y' => 2);
$arr->z = & $arr;
Assert::match( 'stdClass #%h%
   x => 1
   y => 2
   z => stdClass #%h% { RECURSION }
', Dumper::toText($arr) );
