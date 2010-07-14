<?php

/**
 * Test: Nette\Collections\ArrayList::__construct()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\ArrayList;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$arr = array(
	'a' => new Person('Jack'),
	'b' => new Person('Mary'),
	'c' => new ArrayObject(),
);

try {
	T::note("Construct from array");
	$list = new ArrayList($arr, 'Person');
} catch (Exception $e) {
	T::dump( $e );
}

T::note("Construct from array II.");
$list = new ArrayList($arr);
T::dump( $list );



__halt_compiler() ?>

------EXPECT------
Construct from array

Exception InvalidArgumentException: Item must be 'Person' object.

Construct from array II.

%ns%ArrayList(
	"0" => Person(
		"name" private => "Jack"
	)
	"1" => Person(
		"name" private => "Mary"
	)
	"2" => ArrayObject()
)
