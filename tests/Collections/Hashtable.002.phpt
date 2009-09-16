<?php

/**
 * Test: Nette\Collections\Hashtable readonly collection.
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

dump( $hashtable->isFrozen() );
$hashtable->freeze();
dump( $hashtable->isFrozen() );

try {
	output("Adding Jack using []");
	$hashtable['new'] = $jack;
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Adding Jack using add");
	$hashtable->add('new', $jack);
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Removing using unset");
	unset($hashtable['jack']);
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Changing using []");
	$hashtable['jack'] = $jack;
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
bool(FALSE)

bool(TRUE)

Adding Jack using []

Exception InvalidStateException: Cannot modify a frozen object '%ns%Hashtable'.

Adding Jack using add

Exception InvalidStateException: Cannot modify a frozen object '%ns%Hashtable'.

Removing using unset

Exception InvalidStateException: Cannot modify a frozen object '%ns%Hashtable'.

Changing using []

Exception InvalidStateException: Cannot modify a frozen object '%ns%Hashtable'.
