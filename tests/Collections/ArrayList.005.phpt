<?php

/**
 * Test: Nette\Collections\ArrayList and removing items.
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
$list[] = new Person('Jack');
$list[] = new Person('Mary');
$list[] = $larry = new Person('Larry');


T::dump( $list->remove($larry), "Removing Larry" );

T::dump( $list->remove($larry), "Removing Larry second time" );


try {
	T::note("unset -1");
	unset($list[-1]);
} catch (Exception $e) {
	T::dump( $e );
}

try {
	T::note("unset 1");
	unset($list[1]);
} catch (Exception $e) {
	T::dump( $e );
}

T::dump( $list );



__halt_compiler() ?>

------EXPECT------
Removing Larry: bool(TRUE)

Removing Larry second time: bool(FALSE)

unset -1

Exception ArgumentOutOfRangeException:

unset 1

object(%ns%ArrayList) (1) {
	"0" => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
}
