<?php

/**
 * Test: ArrayList and removing items.
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
$list[] = new Person('Jack');
$list[] = new Person('Mary');
$list[] = $larry = new Person('Larry');


message("Removing Larry");
dump( $list->remove($larry) );

message("Removing Larry second time");
dump( $list->remove($larry) );


try {
	message("unset -1");
	unset($list[-1]);
} catch (Exception $e) {
	dump( $e );
}

try {
	message("unset 1");
	unset($list[1]);
} catch (Exception $e) {
	dump( $e );
}

dump( $list );


__halt_compiler();

------EXPECT------
Removing Larry

bool(TRUE)

Removing Larry second time

bool(FALSE)

unset -1

Exception ArgumentOutOfRangeException: 

unset 1

object(ArrayList) (1) {
	"0" => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
}

