<?php

/**
 * Test: Nette\Collections\Hashtable and get & contains, indexOf.
 *
 * @author     David Grudl
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Hashtable;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Collections.inc';



$hashtable = new Hashtable(NULL, 'Person');
$hashtable['jack'] = $jack = new Person('Jack');
$hashtable['mary'] = $mary = new Person('Mary');
$foo = new ArrayObject();



Assert::true( $hashtable->contains($jack), "Contains Jack?" );

Assert::true( $hashtable->contains($mary), "Contains Mary?" );

Assert::false( $hashtable->contains($foo), "Contains foo?" );


Assert::false( isset($hashtable['jim']), "Contains ['jim']?" );

Assert::true( isset($hashtable['jack']), "Contains ['jack']?" );

Assert::true( isset($hashtable['mary']), "Contains ['mary']?" );



Assert::false( isset($hashtable->jim), "Contains ->jim?" );

Assert::false( isset($hashtable->jack), "Contains ->jack?" );

Assert::false( isset($hashtable->mary), "Contains ->mary?" );



Assert::null( $hashtable['jim'], "Getting ['jim']" );

try {
	// Getting ['jim'] with throwKeyNotFound
	$hashtable->throwKeyNotFound();
	$hashtable['jim'];
	Assert::fail('Expected exception');
} catch (Exception $e) {
	//Assert::exception('KeyNotFoundException', '', $e );
}

Assert::equal( new Person("Mary"), $hashtable['mary'], "Getting ['mary']" );
