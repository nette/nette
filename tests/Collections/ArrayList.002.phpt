<?php

/**
 * Test: Nette\Collections\ArrayList readonly collection.
 *
 * @author     David Grudl
 * @category   Nette
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

T::dump( $list->isFrozen() );
$list->freeze();
T::dump( $list->isFrozen() );

try {
	T::note("Adding Jack using []");
	$list[] = $jack;
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Adding Jack using insertAt");
	$list->insertAt(0, $jack);
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Removing using unset");
	unset($list[1]);
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Changing using []");
	$list[1] = $jack;
} catch (Exception $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
bool(FALSE)

bool(TRUE)

Adding Jack using []

Exception InvalidStateException: Cannot modify a frozen object '%ns%ArrayList'.

Adding Jack using insertAt

Exception InvalidStateException: Cannot modify a frozen object '%ns%ArrayList'.

Removing using unset

Exception InvalidStateException: Cannot modify a frozen object '%ns%ArrayList'.

Changing using []

Exception InvalidStateException: Cannot modify a frozen object '%ns%ArrayList'.
