<?php

/**
 * Test: Nette\Collections\ArrayList and getting items & contains(), indexOf()
 *
 * @author     David Grudl
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\ArrayList;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$list = new ArrayList(NULL, 'Person');
$list[] = $jack = new Person('Jack');
$list[] = $mary = new Person('Mary');
$foo = new ArrayObject();



Assert::true( $list->contains($jack), "Contains Jack?" );

Assert::same( 0, $list->indexOf($jack), "indexOf Jack:" );

Assert::true( $list->contains($mary), "Contains Mary?" );

Assert::same( 1, $list->indexOf($mary), "indexOf Mary:" );

Assert::false( $list->contains($foo), "Contains foo?" );

Assert::false( $list->indexOf($foo), "indexOf foo?" );



Assert::false( isset($list[-1]), "Contains index -1?" );

Assert::true( isset($list[0]), "Contains index 0?" );

Assert::false( isset($list[5]), "Contains index 5?" );



try {
	$list[-1];
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('ArgumentOutOfRangeException', '', $e );
}

Assert::equal( new Person("Jack"), $list[0], "Getting index 0" );
