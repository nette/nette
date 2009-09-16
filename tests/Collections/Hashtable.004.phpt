<?php

/**
 * Test: Nette\Collections\Hashtable and removing items.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

/*use Nette\Collections\Hashtable;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';



$hashtable = new Hashtable(NULL, 'Person');
$hashtable['jack'] = $jack = new Person('Jack');
$hashtable['mary'] = new Person('Mary');

dump( $hashtable->remove($jack), "Removing Jack" );

dump( $hashtable->remove($jack), "Removing Jack second time" );


try {
	output("Removing using unset(['unknown'])");
	unset($hashtable['unknown']);
} catch (Exception $e) {
	dump( $e );
}


try {
	output("Removing using unset(->unknown)");
	unset($hashtable->unknown);
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
Removing Jack: bool(TRUE)

Removing Jack second time: bool(FALSE)

Removing using unset(['unknown'])

Removing using unset(->unknown)

Exception MemberAccessException: Cannot unset the property %ns%Hashtable::$unknown.
