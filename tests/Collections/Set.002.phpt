<?php

/**
 * Test: Nette\Collections\Set readonly collection.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Set;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$set = new Set(NULL, 'Person');
$set->append($jack = new Person('Jack'));
$set->append(new Person('Mary'));
$set->append(new Person('Larry'));

T::dump( $set->isFrozen() );
$set->freeze();
T::dump( $set->isFrozen() );

try {
	T::dump( $set->append($jack), "Adding Jack" );
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Removing Jack");
	$set->remove($jack);
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Clearing");
	$set->clear();
} catch (Exception $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
bool(FALSE)

bool(TRUE)

Exception InvalidStateException: Cannot modify a frozen object '%ns%Set'.

Removing Jack

Exception InvalidStateException: Cannot modify a frozen object '%ns%Set'.

Clearing

Exception InvalidStateException: Cannot modify a frozen object '%ns%Set'.
