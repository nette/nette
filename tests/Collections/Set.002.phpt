<?php

/**
 * Test: Set readonly collection.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';

/*use Nette\Collections\Set;*/


$set = new Set(NULL, 'Person');
$set->append($jack = new Person('Jack'));
$set->append(new Person('Mary'));
$set->append(new Person('Larry'));

dump( $set->isFrozen() );
$set->freeze();
dump( $set->isFrozen() );

try {
	message("Adding Jack");
	dump( $set->append($jack) );
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Removing Jack");
	$set->remove($jack);
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Clearing");
	$set->clear();
} catch (Exception $e) {
	dump( $e );
}


__halt_compiler();

------EXPECT------
bool(FALSE)

bool(TRUE)

Adding Jack

Exception InvalidStateException: Cannot modify a frozen object 'Set'.

Removing Jack

Exception InvalidStateException: Cannot modify a frozen object 'Set'.

Clearing

Exception InvalidStateException: Cannot modify a frozen object 'Set'.

