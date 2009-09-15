<?php

/**
 * Test: Set::contains()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';

/*use Nette\Collections\Set;*/


$set = new Set(NULL, 'Person');
$set->append($jack = new Person('Jack'));
$set->append(new Person('Mary'));
$larry = new Person('Larry');
$foo = new ArrayObject;

message("Contains Jack?");
dump( $set->contains($jack) );

message("Contains Larry?");
dump( $set->contains($larry) );

try {
	message("Contains foo?");
	dump( $set->contains($foo) );
} catch (Exception $e) {
	dump( $e );
}


__halt_compiler();

------EXPECT------
Contains Jack?

bool(TRUE)

Contains Larry?

bool(FALSE)

Contains foo?

bool(FALSE)

