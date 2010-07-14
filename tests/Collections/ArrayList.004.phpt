<?php

/**
 * Test: Nette\Collections\ArrayList and getting items & contains(), indexOf()
 *
 * @author     David Grudl
 * @category   Nette
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



T::dump( $list->contains($jack), "Contains Jack?" );

T::dump( $list->indexOf($jack), "indexOf Jack:" );

T::dump( $list->contains($mary), "Contains Mary?" );

T::dump( $list->indexOf($mary), "indexOf Mary:" );

T::dump( $list->contains($foo), "Contains foo?" );

T::dump( $list->indexOf($foo), "indexOf foo?" );



T::dump( isset($list[-1]), "Contains index -1?" );

T::dump( isset($list[0]), "Contains index 0?" );

T::dump( isset($list[5]), "Contains index 5?" );



try {
	T::dump( $list[-1], "Getting index -1" );
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::dump( $list[0], "Getting index 0" );
} catch (Exception $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Contains Jack? TRUE

indexOf Jack: 0

Contains Mary? TRUE

indexOf Mary: 1

Contains foo? FALSE

indexOf foo? FALSE

Contains index -1? FALSE

Contains index 0? TRUE

Contains index 5? FALSE

Exception ArgumentOutOfRangeException:

Getting index 0: Person(
	"name" private => "Jack"
)
