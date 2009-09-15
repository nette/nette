<?php

/**
 * Test: Set adding numeric items.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';

/*use Nette\Collections\Set;*/


$set = new Set(NULL, ':numeric');

message("Adding numeric");
$set->append('10.3');

message("Adding numeric");
$set->append(12.2);

try {
	message("Adding non-numeric");
	$set->append('hello');
} catch (Exception $e) {
	dump( $e );
}


__halt_compiler();

------EXPECT------
Adding numeric

Adding numeric

Adding non-numeric

Exception InvalidArgumentException: Item must be numeric type.

