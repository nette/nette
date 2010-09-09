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

Assert::true( $list->insertAt(0, $larry) );
Assert::equal( array(
	new Person("Larry"),
	new Person("Jack"),
	new Person("Mary"),
), (array) $list );

Assert::true( $list->insertAt(3, $larry) );
Assert::equal( array(
	new Person("Larry"),
	new Person("Jack"),
	new Person("Mary"),
	new Person("Larry"),
), (array) $list );

try {
	$list->insertAt(6, $larry);
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('ArgumentOutOfRangeException', '', $e );
}
