<?php

/**
 * Test: Nette\Collections\Hashtable readonly collection.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Hashtable;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$hashtable = new Hashtable(NULL, 'Person');
$hashtable['jack'] = $jack = new Person('Jack');
$hashtable['mary'] = new Person('Mary');

T::dump( $hashtable->isFrozen() );
$hashtable->freeze();
T::dump( $hashtable->isFrozen() );

try {
	T::note("Adding Jack using []");
	$hashtable['new'] = $jack;
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Adding Jack using add");
	$hashtable->add('new', $jack);
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Removing using unset");
	unset($hashtable['jack']);
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Changing using []");
	$hashtable['jack'] = $jack;
} catch (Exception $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
FALSE

TRUE

Adding Jack using []

Exception InvalidStateException: Cannot modify a frozen object '%ns%Hashtable'.

Adding Jack using add

Exception InvalidStateException: Cannot modify a frozen object '%ns%Hashtable'.

Removing using unset

Exception InvalidStateException: Cannot modify a frozen object '%ns%Hashtable'.

Changing using []

Exception InvalidStateException: Cannot modify a frozen object '%ns%Hashtable'.
