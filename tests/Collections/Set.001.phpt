<?php

/**
 * Test: Nette\Collections\Set adding items.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Set;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$set = new Set(NULL, 'Person');

$jack = new Person('Jack');
$mary = new Person('Mary');
$larry = new Person('Larry');
$foo = new ArrayObject();


T::dump( $set->append($jack), "Adding Jack" );

T::dump( $set->append($mary), "Adding Mary" );

T::dump( $set->append($mary), "Adding Mary second time" );

T::dump( $set->append($larry), "Adding Larry" );

try {
	T::dump( $set->append($foo), "Adding foo" );
} catch (Exception $e) {
	T::dump( $e );
}



T::dump( $set->count(), 'count:' );
T::dump( count($set) );


T::dump( $set );

T::dump( (array) $set );



T::note("Get Interator:");
foreach ($set as $person) {
	echo $person->sayHi();
}



T::note("Clearing");
$set->clear();

T::dump( $set );



__halt_compiler() ?>

------EXPECT------
Adding Jack: bool(TRUE)

Adding Mary: bool(TRUE)

Adding Mary second time: bool(FALSE)

Adding Larry: bool(TRUE)

Exception InvalidArgumentException: Item must be 'Person' object.

count: int(3)

int(3)

object(%ns%Set) (3) {
	"%h%" => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
	"%h%" => object(Person) (1) {
		"name" private => string(4) "Mary"
	}
	"%h%" => object(Person) (1) {
		"name" private => string(5) "Larry"
	}
}

array(3) {
	"%h%" => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
	"%h%" => object(Person) (1) {
		"name" private => string(4) "Mary"
	}
	"%h%" => object(Person) (1) {
		"name" private => string(5) "Larry"
	}
}

Get Interator:

My name is Jack

My name is Mary

My name is Larry

Clearing

object(%ns%Set) (0) {}
