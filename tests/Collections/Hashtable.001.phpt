<?php

/**
 * Test: Nette\Collections\Hashtable adding items.
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

$jack = new Person('Jack');
$mary = new Person('Mary');
$foo = new ArrayObject();


output("Adding Jack");
$hashtable->add('jack', $jack);

try {
	output("Adding invalid key");
	$hashtable->add($foo, $foo);
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Adding foo");
	$hashtable->add('foo', $foo);
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Adding Mary using []");
	$hashtable[] = $mary;
} catch (Exception $e) {
	dump( $e );
}

output("Adding Mary using ['mary']");
$hashtable['mary'] = $mary;

try {
	output("Adding Mary twice using ['mary']");
	$hashtable['mary'] = $mary;
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Adding Mary twice using add()");
	$hashtable->add('mary', $mary);
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Adding Mary twice using __set()");
	$hashtable->mary = $mary;
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Adding Jack using append");
	$hashtable->append($jack);
} catch (Exception $e) {
	dump( $e );
}




dump( $hashtable->count(), 'count:' );
dump( count($hashtable) );


dump( $hashtable );
dump( (array) $hashtable );
dump( $hashtable->getKeys(), "getKeys:" );



output("Get Interator:");
foreach ($hashtable as $key => $person) {
	echo $key, ' => ', $person->sayHi();
}



output("Clearing");
$hashtable->clear();

dump( $hashtable );



__halt_compiler();

------EXPECT------
Adding Jack

Adding invalid key

Exception InvalidArgumentException: Key must be either a string or an integer, object given.

Adding foo

Exception InvalidArgumentException: Item must be 'Person' object.

Adding Mary using []

Exception InvalidArgumentException: Key must be either a string or an integer, NULL given.

Adding Mary using ['mary']

Adding Mary twice using ['mary']

Adding Mary twice using add()

Exception InvalidStateException: An element with the same key already exists.

Adding Mary twice using __set()

Exception MemberAccessException: Cannot assign to an undeclared property %ns%Hashtable::$mary.

Adding Jack using append

Exception NotSupportedException:

count: int(2)

int(2)

object(%ns%Hashtable) (2) {
	"jack" => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
	"mary" => object(Person) (1) {
		"name" private => string(4) "Mary"
	}
}

array(2) {
	"jack" => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
	"mary" => object(Person) (1) {
		"name" private => string(4) "Mary"
	}
}

getKeys: array(2) {
	0 => string(4) "jack"
	1 => string(4) "mary"
}

Get Interator:

jack => My name is Jack

mary => My name is Mary

Clearing

object(%ns%Hashtable) (0) {}
