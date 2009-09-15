<?php

/**
 * Test: Hashtable adding items.
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

$jack = new Person('Jack');
$mary = new Person('Mary');
$foo = new ArrayObject();


message("Adding Jack");
$hashtable->add('jack', $jack);

try {
	message("Adding invalid key");
	$hashtable->add($foo, $foo);
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Adding foo");
	$hashtable->add('foo', $foo);
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Adding Mary using []");
	$hashtable[] = $mary;
} catch (Exception $e) {
	dump( $e );
}

message("Adding Mary using ['mary']");
$hashtable['mary'] = $mary;

try {
	message("Adding Mary twice using ['mary']");
	$hashtable['mary'] = $mary;
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Adding Mary twice using add()");
	$hashtable->add('mary', $mary);
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Adding Mary twice using __set()");
	$hashtable->mary = $mary;
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Adding Jack using append");
	$hashtable->append($jack);
} catch (Exception $e) {
	dump( $e );
}




message('count:');
dump( $hashtable->count() );
dump( count($hashtable) );


dump( $hashtable );
dump( (array) $hashtable );
message("getKeys:");
dump( $hashtable->getKeys() );



message("Get Interator:");
foreach ($hashtable as $key => $person) {
	echo $key, ' => ', $person->sayHi();
}



message("Clearing");
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

Exception MemberAccessException: Cannot assign to an undeclared property Hashtable::$mary.

Adding Jack using append

Exception NotSupportedException: 

count:

int(2)

int(2)

object(Hashtable) (2) {
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

getKeys:

array(2) {
	0 => string(4) "jack"
	1 => string(4) "mary"
}

Get Interator:

jack => My name is Jack

mary => My name is Mary

Clearing

object(Hashtable) (0) {}

