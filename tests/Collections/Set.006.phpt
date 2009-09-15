<?php

/**
 * Test: Set and removing items.
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
$set->append(new Person('Jack'));
$set->append(new Person('Mary'));
$set->append($larry = new Person('Larry'));
$foo = new ArrayObject;

message("Removing Larry");
dump( $set->remove($larry) );

message("Removing Larry second time");
dump( $set->remove($larry) );

try {
	message("Removing foo");
	dump( $set->remove($foo) );
} catch (Exception $e) {
	dump( $e );
}

dump( $set );



__halt_compiler();

------EXPECT------
Removing Larry

bool(TRUE)

Removing Larry second time

bool(FALSE)

Removing foo

bool(FALSE)

object(Set) (2) {
	"%h%" => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
	"%h%" => object(Person) (1) {
		"name" private => string(4) "Mary"
	}
}

