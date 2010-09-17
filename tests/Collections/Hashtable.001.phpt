<?php

/**
 * Test: Nette\Collections\Hashtable adding items.
 *
 * @author     David Grudl
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Hashtable;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Collections.inc';



$hashtable = new Hashtable(NULL, 'Person');

$jack = new Person('Jack');
$mary = new Person('Mary');
$foo = new ArrayObject();


// Adding Jack
$hashtable->add('jack', $jack);

try {
	// Adding invalid key
	$hashtable->add($foo, $foo);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Key must be either a string or an integer, object given.", $e );
}

try {
	// Adding foo
	$hashtable->add('foo', $foo);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Item must be 'Person' object.", $e );
}

try {
	// Adding Mary using []
	$hashtable[] = $mary;
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Key must be either a string or an integer, NULL given.", $e );
}

// Adding Mary using ['mary']
$hashtable['mary'] = $mary;

// Adding Mary twice using ['mary']
$hashtable['mary'] = $mary;

try {
	// Adding Mary twice using add()
	$hashtable->add('mary', $mary);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidStateException', "An element with the same key already exists.", $e );
}

try {
	// Adding Mary twice using __set()
	$hashtable->mary = $mary;
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('MemberAccessException', "Cannot assign to an undeclared property %ns%Hashtable::\$mary.", $e );
}

try {
	// Adding Jack using append
	$hashtable->append($jack);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('NotSupportedException', '', $e );
}



Assert::same( 2, $hashtable->count(), 'count:' );
Assert::same( 2, count($hashtable) );


Assert::equal( array(
	"jack" => new Person("Jack"),
	"mary" => new Person("Mary"),
), (array) $hashtable );
Assert::same( array(
	"jack",
	"mary",
), $hashtable->getKeys(), "getKeys:" );
