<?php

/**
 * Test: Hashtable and get & contains, indexOf.
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
$hashtable['jack'] = $jack = new Person('Jack');
$hashtable['mary'] = $mary = new Person('Mary');
$foo = new ArrayObject();



message("Contains Jack?");
dump( $hashtable->contains($jack) );

message("Contains Mary?");
dump( $hashtable->contains($mary) );

try {
	message("Contains foo?");
	dump( $hashtable->contains($foo) );
} catch (Exception $e) {
	dump( $e );
}


message("Contains ['jim']?");
dump( isset($hashtable['jim']) );

message("Contains ['jack']?");
dump( isset($hashtable['jack']) );

message("Contains ['mary']?");
dump( isset($hashtable['mary']) );



message("Contains ->jim?");
dump( isset($hashtable->jim) );

message("Contains ->jack?");
dump( isset($hashtable->jack) );

message("Contains ->mary?");
dump( isset($hashtable->mary) );



try {
	message("Getting ['jim']");
	dump( $hashtable['jim'] );
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Getting ['jim'] with throwKeyNotFound");
	$hashtable->throwKeyNotFound();
	dump( $hashtable['jim'] );
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Getting ['mary']");
	dump( $hashtable['mary'] );
} catch (Exception $e) {
	dump( $e );
}


try {
	message("Getting ->jim");
	dump( $hashtable->jim );
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Getting ->mary");
	dump( $hashtable->mary );
} catch (Exception $e) {
	dump( $e );
}



try {
	message("Getting get('jim')");
	dump( $hashtable->get('jim', 'default') );
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Getting get('mary')");
	dump( $hashtable->get('mary', 'default') );
} catch (Exception $e) {
	dump( $e );
}



message("search Jack:");
dump( $hashtable->search($jack) );

message("search Mary:");
dump( $hashtable->search($mary) );


__halt_compiler();

------EXPECT------
Contains Jack?

bool(TRUE)

Contains Mary?

bool(TRUE)

Contains foo?

bool(FALSE)

Contains ['jim']?

bool(FALSE)

Contains ['jack']?

bool(TRUE)

Contains ['mary']?

bool(TRUE)

Contains ->jim?

bool(FALSE)

Contains ->jack?

bool(FALSE)

Contains ->mary?

bool(FALSE)

Getting ['jim']

NULL

Getting ['jim'] with throwKeyNotFound

Exception KeyNotFoundException: 

Getting ['mary']

object(Person) (1) {
	"name" private => string(4) "Mary"
}

Getting ->jim

Exception MemberAccessException: Cannot read an undeclared property Hashtable::$jim.

Getting ->mary

Exception MemberAccessException: Cannot read an undeclared property Hashtable::$mary.

Getting get('jim')

string(7) "default"

Getting get('mary')

object(Person) (1) {
	"name" private => string(4) "Mary"
}

search Jack:

string(4) "jack"

search Mary:

string(4) "mary"

