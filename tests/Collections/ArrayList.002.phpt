<?php

/**
 * Test: Nette\Collections\ArrayList readonly collection.
 *
 * @author     David Grudl
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\ArrayList;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$list = new ArrayList(NULL, 'Person');
$jack = new Person('Jack');
$list[] = new Person('Mary');
$list[] = new Person('Larry');

Assert::false( $list->isFrozen() );
$list->freeze();
Assert::true( $list->isFrozen() );

try {
	// Adding Jack using []
	$list[] = $jack;
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%ArrayList'.", $e );
}

try {
	// Adding Jack using insertAt
	$list->insertAt(0, $jack);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%ArrayList'.", $e );
}

try {
	// Removing using unset
	unset($list[1]);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%ArrayList'.", $e);
}

try {
	// Changing using []
	$list[1] = $jack;
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "Cannot modify a frozen object '%ns%ArrayList'.", $e );
}
