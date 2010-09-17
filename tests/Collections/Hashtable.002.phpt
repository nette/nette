<?php

/**
 * Test: Nette\Collections\Hashtable readonly collection.
 *
 * @author     David Grudl
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Hashtable;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Collections.inc';



$hashtable = new Hashtable(NULL, 'Person');
$hashtable['jack'] = $jack = new Person('Jack');
$hashtable['mary'] = new Person('Mary');

Assert::false( $hashtable->isFrozen() );
$hashtable->freeze();
Assert::true( $hashtable->isFrozen() );

try {
	// Adding Jack using []
	$hashtable['new'] = $jack;
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%Hashtable'.", $e );
}

try {
	// Adding Jack using add
	$hashtable->add('new', $jack);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%Hashtable'.", $e );
}

try {
	// Removing using unset
	unset($hashtable['jack']);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%Hashtable'.", $e );
}

try {
	// Changing using []
	$hashtable['jack'] = $jack;
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%Hashtable'.", $e );
}
