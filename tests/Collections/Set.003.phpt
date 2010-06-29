<?php

/**
 * Test: Nette\Collections\Set adding numeric items.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Set;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$set = new Set(NULL, ':numeric');

T::note("Adding numeric");
$set->append('10.3');

T::note("Adding numeric");
$set->append(12.2);

try {
	T::note("Adding non-numeric");
	$set->append('hello');
} catch (Exception $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Adding numeric

Adding numeric

Adding non-numeric

Exception InvalidArgumentException: Item must be numeric type.
