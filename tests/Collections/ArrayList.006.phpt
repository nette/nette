<?php

/**
 * Test: Nette\Collections\ArrayList::__construct()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

/*use Nette\Collections\ArrayList;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';



$arr = array(
	'a' => new Person('Jack'),
	'b' => new Person('Mary'),
	'c' => new ArrayObject(),
);

try {
	output("Construct from array");
	$list = new ArrayList($arr, 'Person');
} catch (Exception $e) {
	dump( $e );
}

output("Construct from array II.");
$list = new ArrayList($arr);
dump( $list );



__halt_compiler();

------EXPECT------
Construct from array

Exception InvalidArgumentException: Item must be 'Person' object.

Construct from array II.

object(%ns%ArrayList) (3) {
	"0" => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
	"1" => object(Person) (1) {
		"name" private => string(4) "Mary"
	}
	"2" => object(ArrayObject) (0) {}
}
