<?php

/**
 * Test: Nette\Collections\ArrayList::insertAt()
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
$list[] = new Person('Jack');
$list[] = new Person('Mary');

$larry = new Person('Larry');

dump( $list->insertAt(0, $larry) );
dump( (array) $list);

dump( $list->insertAt(3, $larry) );
dump( (array) $list);

try {
	dump( $list->insertAt(6, $larry) );
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
bool(TRUE)

array(3) {
	0 => object(Person) (1) {
		"name" private => string(5) "Larry"
	}
	1 => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
	2 => object(Person) (1) {
		"name" private => string(4) "Mary"
	}
}

bool(TRUE)

array(4) {
	0 => object(Person) (1) {
		"name" private => string(5) "Larry"
	}
	1 => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
	2 => object(Person) (1) {
		"name" private => string(4) "Mary"
	}
	3 => object(Person) (1) {
		"name" private => string(5) "Larry"
	}
}

Exception ArgumentOutOfRangeException:
