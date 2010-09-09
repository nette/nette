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

Assert::false( $set->isFrozen() );
$set->freeze();
Assert::true( $set->isFrozen() );

try {
	$set->append($jack);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%Set'.", $e );
}

try {
	// Removing Jack
	$set->remove($jack);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%Set'.", $e );
}

try {
	// Clearing
	$set->clear();
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%Set'.", $e );
}

