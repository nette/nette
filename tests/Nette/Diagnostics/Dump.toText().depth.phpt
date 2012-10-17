<?php

/**
 * Test: Nette\Diagnostics\Dump::toText() depth & truncate
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 */

use Nette\Diagnostics\Dump;



require __DIR__ . '/../bootstrap.php';



$arr = array(
	'long' => str_repeat('Nette Framework', 1000),

	array(
		array(
			array('hello' => 'world'),
		),
	),

	'long2' => str_repeat('Nette Framework', 1000),

	(object) array(
		(object) array(
			(object) array('hello' => 'world'),
		),
	),
);
$arr[] = &$arr;


Assert::match( 'array (5)
   long => "Nette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette Framework ... " (15000)
   0 => array (1)
   |  0 => array (1)
   |  |  0 => array (1)
   |  |  |  hello => "world" (5)
   long2 => "Nette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette Framework ... " (15000)
   1 => stdClass (1)
   |  0 => stdClass (1)
   |  |  0 => stdClass (1)
   |  |  |  hello => "world" (5)
   2 => array (5)
   |  long => "Nette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette Framework ... " (15000)
   |  0 => array (1)
   |  |  0 => array (1)
   |  |  |  0 => array (1) [ ... ]
   |  long2 => "Nette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette FrameworkNette Framework ... " (15000)
   |  1 => stdClass (1)
   |  |  0 => stdClass (1)
   |  |  |  0 => stdClass (1) { ... }
   |  2 => array (5) [ RECURSION ]
', Dump::toText($arr) );



Assert::match( 'array (5)
   long => "Nette FrameworkNette FrameworkNette FrameworkNette ... " (15000)
   0 => array (1)
   |  0 => array (1) [ ... ]
   long2 => "Nette FrameworkNette FrameworkNette FrameworkNette ... " (15000)
   1 => stdClass (1)
   |  0 => stdClass (1) { ... }
   2 => array (5)
   |  long => "Nette FrameworkNette FrameworkNette FrameworkNette ... " (15000)
   |  0 => array (1) [ ... ]
   |  long2 => "Nette FrameworkNette FrameworkNette FrameworkNette ... " (15000)
   |  1 => stdClass (1) { ... }
   |  2 => array (5) [ RECURSION ]
', Dump::toText($arr, array(Dump::DEPTH => 2, Dump::TRUNCATE => 50)) );
