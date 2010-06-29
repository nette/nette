<?php

/**
 * Test: Nette\Collections\Hashtable adding items.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Hashtable;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$hashtable = new Hashtable(NULL, 'Person');

$jack = new Person('Jack');
$mary = new Person('Mary');
$foo = new ArrayObject();


T::note("Adding Jack");
$hashtable->add('jack', $jack);

try {
	T::note("Adding invalid key");
	$hashtable->add($foo, $foo);
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Adding foo");
	$hashtable->add('foo', $foo);
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Adding Mary using []");
	$hashtable[] = $mary;
} catch (Exception $e) {
	T::dump( $e );
}

T::note("Adding Mary using ['mary']");
$hashtable['mary'] = $mary;

try {
	T::note("Adding Mary twice using ['mary']");
	$hashtable['mary'] = $mary;
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Adding Mary twice using add()");
	$hashtable->add('mary', $mary);
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Adding Mary twice using __set()");
	$hashtable->mary = $mary;
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Adding Jack using append");
	$hashtable->append($jack);
} catch (Exception $e) {
	T::dump( $e );
}



T::dump( $hashtable->count(), 'count:' );
T::dump( count($hashtable) );


T::dump( $hashtable );
T::dump( (array) $hashtable );
T::dump( $hashtable->getKeys(), "getKeys:" );



T::note("Get Interator:");
foreach ($hashtable as $key => $person) {
	echo $key, ' => ', $person->sayHi();
}



T::note("Clearing");
$hashtable->clear();

T::dump( $hashtable );



__halt_compiler() ?>

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
