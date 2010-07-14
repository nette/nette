<?php

/**
 * Test: Nette\Collections\Set::__construct()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Set;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$arr = array(
	'a' => new Person('Jack'),
	'b' => new Person('Mary'),
	'c' => new ArrayObject(),
);

try {
	T::note("Construct from array");
	$set = new Set($arr, 'Person');
} catch (Exception $e) {
	T::dump( $e );
}

T::note("Construct from array II.");
$set = new Set($arr);
T::dump( $set );


try {
	T::note("Construct from collection");
	$set2 = new Set($set, 'Person');

} catch (Exception $e) {
	T::dump( $e );
}

T::note("Construct from collection II.");
$set2 = new Set($set);
T::dump( $set2 );



__halt_compiler() ?>

------EXPECT------
Construct from array

Exception InvalidArgumentException: Item must be 'Person' object.

Construct from array II.

%ns%Set(
	"%h%" => Person(
		"name" private => "Jack"
	)
	"%h%" => Person(
		"name" private => "Mary"
	)
	"%h%" => ArrayObject()
)

Construct from collection

Exception InvalidArgumentException: Item must be 'Person' object.

Construct from collection II.

%ns%Set(
	"%h%" => Person(
		"name" private => "Jack"
	)
	"%h%" => Person(
		"name" private => "Mary"
	)
	"%h%" => ArrayObject()
)
