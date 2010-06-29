<?php

/**
 * Test: Nette\Collections\Hashtable and get & contains, indexOf.
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
$hashtable['jack'] = $jack = new Person('Jack');
$hashtable['mary'] = $mary = new Person('Mary');
$foo = new ArrayObject();



T::dump( $hashtable->contains($jack), "Contains Jack?" );

T::dump( $hashtable->contains($mary), "Contains Mary?" );

try {
	T::dump( $hashtable->contains($foo), "Contains foo?" );
} catch (Exception $e) {
	T::dump( $e );
}


T::dump( isset($hashtable['jim']), "Contains ['jim']?" );

T::dump( isset($hashtable['jack']), "Contains ['jack']?" );

T::dump( isset($hashtable['mary']), "Contains ['mary']?" );



T::dump( isset($hashtable->jim), "Contains ->jim?" );

T::dump( isset($hashtable->jack), "Contains ->jack?" );

T::dump( isset($hashtable->mary), "Contains ->mary?" );



try {
	T::dump( $hashtable['jim'], "Getting ['jim']" );
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("Getting ['jim'] with throwKeyNotFound");
	$hashtable->throwKeyNotFound();
	T::dump( $hashtable['jim'] );
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::dump( $hashtable['mary'], "Getting ['mary']" );
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::dump( $hashtable->jim, "Getting ->jim" );
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::dump( $hashtable->mary, "Getting ->mary" );
} catch (Exception $e) {
	T::dump( $e );
}



try {
	T::dump( $hashtable->get('jim', 'default'), "Getting get('jim')" );
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::dump( $hashtable->get('mary', 'default'), "Getting get('mary')" );
} catch (Exception $e) {
	T::dump( $e );
}



T::dump( $hashtable->search($jack), "search Jack:" );

T::dump( $hashtable->search($mary), "search Mary:" );



__halt_compiler() ?>

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
