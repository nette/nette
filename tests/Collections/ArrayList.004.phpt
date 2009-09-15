<?php

/**
 * Test: ArrayList and getting items & contains(), indexOf()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';

/*use Nette\Collections\ArrayList;*/


$list = new ArrayList(NULL, 'Person');
$list[] = $jack = new Person('Jack');
$list[] = $mary = new Person('Mary');
$foo = new ArrayObject();



message("Contains Jack?");
dump( $list->contains($jack) );

message("indexOf Jack:");
dump( $list->indexOf($jack) );

message("Contains Mary?");
dump( $list->contains($mary) );

message("indexOf Mary:");
dump( $list->indexOf($mary) );

message("Contains foo?");
dump( $list->contains($foo) );

message("indexOf foo?");
dump( $list->indexOf($foo) );



message("Contains index -1?");
dump( isset($list[-1]) );

message("Contains index 0?");
dump( isset($list[0]) );

message("Contains index 5?");
dump( isset($list[5]) );



try {
	message("Getting index -1");
	dump( $list[-1] );
} catch (Exception $e) {
	dump( $e );
}

try {
	message("Getting index 0");
	dump( $list[0] );
} catch (Exception $e) {
	dump( $e );
}


__halt_compiler();

------EXPECT------
Contains Jack?

bool(TRUE)

indexOf Jack:

int(0)

Contains Mary?

bool(TRUE)

indexOf Mary:

int(1)

Contains foo?

bool(FALSE)

indexOf foo?

bool(FALSE)

Contains index -1?

bool(FALSE)

Contains index 0?

bool(TRUE)

Contains index 5?

bool(FALSE)

Getting index -1

Exception ArgumentOutOfRangeException: 

Getting index 0

object(Person) (1) {
	"name" private => string(4) "Jack"
}

