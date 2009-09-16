<?php

/**
 * Test: Nette\Collections\Set readonly collection.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

/*use Nette\Collections\Set;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';



$set = new Set(NULL, 'Person');
$set->append($jack = new Person('Jack'));
$set->append(new Person('Mary'));
$set->append(new Person('Larry'));

dump( $set->isFrozen() );
$set->freeze();
dump( $set->isFrozen() );

try {
	dump( $set->append($jack), "Adding Jack" );
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Removing Jack");
	$set->remove($jack);
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Clearing");
	$set->clear();
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
bool(FALSE)

bool(TRUE)

Exception InvalidStateException: Cannot modify a frozen object '%ns%Set'.

Removing Jack

Exception InvalidStateException: Cannot modify a frozen object '%ns%Set'.

Clearing

Exception InvalidStateException: Cannot modify a frozen object '%ns%Set'.
