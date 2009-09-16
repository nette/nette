<?php

/**
 * Test: Nette\Collections\Set and removing items.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

/*use Nette\Collections\Set;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';



$set = new Set(NULL, 'Person');
$set->append(new Person('Jack'));
$set->append(new Person('Mary'));
$set->append($larry = new Person('Larry'));
$foo = new ArrayObject;

dump( $set->remove($larry), "Removing Larry" );

dump( $set->remove($larry), "Removing Larry second time" );

try {
	dump( $set->remove($foo), "Removing foo" );
} catch (Exception $e) {
	dump( $e );
}

dump( $set );



__halt_compiler();

------EXPECT------
Removing Larry: bool(TRUE)

Removing Larry second time: bool(FALSE)

Removing foo: bool(FALSE)

object(%ns%Set) (2) {
	"%h%" => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
	"%h%" => object(Person) (1) {
		"name" private => string(4) "Mary"
	}
}
