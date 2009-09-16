<?php

/**
 * Test: Nette\Collections\Hashtable and get & contains, indexOf.
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
$hashtable['jack'] = $jack = new Person('Jack');
$hashtable['mary'] = $mary = new Person('Mary');
$foo = new ArrayObject();



dump( $hashtable->contains($jack), "Contains Jack?" );

dump( $hashtable->contains($mary), "Contains Mary?" );

try {
	dump( $hashtable->contains($foo), "Contains foo?" );
} catch (Exception $e) {
	dump( $e );
}


dump( isset($hashtable['jim']), "Contains ['jim']?" );

dump( isset($hashtable['jack']), "Contains ['jack']?" );

dump( isset($hashtable['mary']), "Contains ['mary']?" );



dump( isset($hashtable->jim), "Contains ->jim?" );

dump( isset($hashtable->jack), "Contains ->jack?" );

dump( isset($hashtable->mary), "Contains ->mary?" );



try {
	dump( $hashtable['jim'], "Getting ['jim']" );
} catch (Exception $e) {
	dump( $e );
}

try {
	output("Getting ['jim'] with throwKeyNotFound");
	$hashtable->throwKeyNotFound();
	dump( $hashtable['jim'] );
} catch (Exception $e) {
	dump( $e );
}

try {
	dump( $hashtable['mary'], "Getting ['mary']" );
} catch (Exception $e) {
	dump( $e );
}


try {
	dump( $hashtable->jim, "Getting ->jim" );
} catch (Exception $e) {
	dump( $e );
}

try {
	dump( $hashtable->mary, "Getting ->mary" );
} catch (Exception $e) {
	dump( $e );
}



try {
	dump( $hashtable->get('jim', 'default'), "Getting get('jim')" );
} catch (Exception $e) {
	dump( $e );
}

try {
	dump( $hashtable->get('mary', 'default'), "Getting get('mary')" );
} catch (Exception $e) {
	dump( $e );
}



dump( $hashtable->search($jack), "search Jack:" );

dump( $hashtable->search($mary), "search Mary:" );



__halt_compiler();

------EXPECT------
Contains Jack? bool(TRUE)

Contains Mary? bool(TRUE)

Contains foo? bool(FALSE)

Contains ['jim']? bool(FALSE)

Contains ['jack']? bool(TRUE)

Contains ['mary']? bool(TRUE)

Contains ->jim? bool(FALSE)

Contains ->jack? bool(FALSE)

Contains ->mary? bool(FALSE)

Getting ['jim']: NULL

Getting ['jim'] with throwKeyNotFound

Exception %ns%KeyNotFoundException:

Getting ['mary']: object(Person) (1) {
	"name" private => string(4) "Mary"
}

Exception MemberAccessException: Cannot read an undeclared property %ns%Hashtable::$jim.

Exception MemberAccessException: Cannot read an undeclared property %ns%Hashtable::$mary.

Getting get('jim'): string(7) "default"

Getting get('mary'): object(Person) (1) {
	"name" private => string(4) "Mary"
}

search Jack: string(4) "jack"

search Mary: string(4) "mary"
