<?php

/**
 * Test: Nette\Collections\ArrayList::insertAt()
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

$larry = new Person('Larry');

T::dump( $list->insertAt(0, $larry) );
T::dump( (array) $list);

T::dump( $list->insertAt(3, $larry) );
T::dump( (array) $list);

try {
	T::dump( $list->insertAt(6, $larry) );
} catch (Exception $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
TRUE

array(
	Person(
		"name" private => "Larry"
	)
	Person(
		"name" private => "Jack"
	)
	Person(
		"name" private => "Mary"
	)
)

TRUE

array(
	Person(
		"name" private => "Larry"
	)
	Person(
		"name" private => "Jack"
	)
	Person(
		"name" private => "Mary"
	)
	Person(
		"name" private => "Larry"
	)
)

Exception ArgumentOutOfRangeException:
