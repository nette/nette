<?php

/**
 * Test: Nette\Collections\ArrayList and getting items & contains(), indexOf()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

/*use Nette\Collections\ArrayList;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';



$list = new ArrayList(NULL, 'Person');
$list[] = $jack = new Person('Jack');
$list[] = $mary = new Person('Mary');
$foo = new ArrayObject();



dump( $list->contains($jack), "Contains Jack?" );

dump( $list->indexOf($jack), "indexOf Jack:" );

dump( $list->contains($mary), "Contains Mary?" );

dump( $list->indexOf($mary), "indexOf Mary:" );

dump( $list->contains($foo), "Contains foo?" );

dump( $list->indexOf($foo), "indexOf foo?" );



dump( isset($list[-1]), "Contains index -1?" );

dump( isset($list[0]), "Contains index 0?" );

dump( isset($list[5]), "Contains index 5?" );



try {
	dump( $list[-1], "Getting index -1" );
} catch (Exception $e) {
	dump( $e );
}

try {
	dump( $list[0], "Getting index 0" );
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
Contains Jack? bool(TRUE)

indexOf Jack: int(0)

Contains Mary? bool(TRUE)

indexOf Mary: int(1)

Contains foo? bool(FALSE)

indexOf foo? bool(FALSE)

Contains index -1? bool(FALSE)

Contains index 0? bool(TRUE)

Contains index 5? bool(FALSE)

Exception ArgumentOutOfRangeException:

Getting index 0: object(Person) (1) {
	"name" private => string(4) "Jack"
}
