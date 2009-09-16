<?php

/**
 * Test: Nette\Collections\Set adding numeric items.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

/*use Nette\Collections\Set;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';



$set = new Set(NULL, ':numeric');

output("Adding numeric");
$set->append('10.3');

output("Adding numeric");
$set->append(12.2);

try {
	output("Adding non-numeric");
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
