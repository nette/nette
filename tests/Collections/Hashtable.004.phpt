<?php

/**
 * Test: Nette\Collections\Hashtable and removing items.
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

T::dump( $hashtable->remove($jack), "Removing Jack" );

T::dump( $hashtable->remove($jack), "Removing Jack second time" );


try {
	T::note("Removing using unset(['unknown'])");
	unset($hashtable['unknown']);
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::note("Removing using unset(->unknown)");
	unset($hashtable->unknown);
} catch (Exception $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Removing Jack: TRUE

Removing Jack second time: FALSE

Removing using unset(['unknown'])

Removing using unset(->unknown)

Exception MemberAccessException: Cannot unset the property %ns%Hashtable::$unknown.
