<?php

/**
 * Test: Nette\Collections\ArrayList readonly collection.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

/*use Nette\Collections\ArrayList;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';



$list = new ArrayList(NULL, 'Person');
$jack = new Person('Jack');
$list[] = new Person('Mary');
$list[] = new Person('Larry');

dump( $list->isFrozen() );
$list->freeze();
dump( $list->isFrozen() );

try {
	output("Adding Jack using []");
	$list[] = $jack;
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Adding Jack using insertAt");
	$list->insertAt(0, $jack);
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Removing using unset");
	unset($list[1]);
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Changing using []");
	$list[1] = $jack;
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

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
