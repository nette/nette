<?php

/**
 * Test: Set adding items.
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

$jack = new Person('Jack');
$mary = new Person('Mary');
$larry = new Person('Larry');
$foo = new ArrayObject();


message("Adding Jack");
dump( $set->append($jack) );

message("Adding Mary");
dump( $set->append($mary) );

message("Adding Mary second time");
dump( $set->append($mary) );

message("Adding Larry");
dump( $set->append($larry) );

try {
	message("Adding foo");
	dump( $set->append($foo) );
} catch (Exception $e) {
	dump( $e );
}



message('count:');
dump( $set->count() );
dump( count($set) );


dump( $set );

dump( (array) $set );



message("Get Interator:");
foreach ($set as $person) {
	echo $person->sayHi();
}



message("Clearing");
$set->clear();

dump( $set );


__halt_compiler();

------EXPECT------
Adding Jack

bool(TRUE)

Adding Mary

bool(TRUE)

Adding Mary second time

bool(FALSE)

Adding Larry

bool(TRUE)

Adding foo

Exception InvalidArgumentException: Item must be 'Person' object.

count:

int(3)

int(3)

object(Set) (3) {
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

object(Set) (0) {}

