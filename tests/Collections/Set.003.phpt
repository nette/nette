<?php

/**
 * Test: Nette\Collections\Set adding numeric items.
 *
 * @author     David Grudl
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Set;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$set = new Set(NULL, ':numeric');

// Adding numeric
$set->append('10.3');

// Adding numeric
$set->append(12.2);

try {
	// Adding non-numeric
	$set->append('hello');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Item must be numeric type.", $e );
}
