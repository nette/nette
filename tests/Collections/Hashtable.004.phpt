<?php

/**
 * Test: Hashtable and removing items.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';

/*use Nette\Collections\Hashtable;*/


$hashtable = new Hashtable(NULL, 'Person');
$hashtable['jack'] = $jack = new Person('Jack');
$hashtable['mary'] = new Person('Mary');

message("Removing Jack");
dump( $hashtable->remove($jack) );

message("Removing Jack second time");
dump( $hashtable->remove($jack) );


try {
	message("Removing using unset(['unknown'])");
	unset($hashtable['unknown']);
} catch (Exception $e) {
	dump( $e );
}


try {
	message("Removing using unset(->unknown)");
	unset($hashtable->unknown);
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
Removing Jack

bool(TRUE)

Removing Jack second time

bool(FALSE)

Removing using unset(['unknown'])

Removing using unset(->unknown)

Exception MemberAccessException: Cannot unset the property Hashtable::$unknown.

