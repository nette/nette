<?php

/**
 * Test: Nette\Collections\Hashtable::__construct()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

/*use Nette\Collections\Hashtable;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';



$arr = array(
	'a' => new Person('Jack'),
	'b' => new Person('Mary'),
	'c' => new ArrayObject(),
);

try {
	output("Construct from array");
	$hashtable = new Hashtable($arr, 'Person');
} catch (Exception $e) {
	dump( $e );
}

output("Construct from array II.");
$hashtable = new Hashtable($arr);
dump( $hashtable );



__halt_compiler();

------EXPECT------
Construct from array

Exception InvalidArgumentException: Item must be 'Person' object.

Construct from array II.

object(%ns%Hashtable) (3) {
	"a" => object(Person) (1) {
		"name" private => string(4) "Jack"
	}
	"b" => object(Person) (1) {
		"name" private => string(4) "Mary"
	}
	"c" => object(ArrayObject) (0) {}
}
