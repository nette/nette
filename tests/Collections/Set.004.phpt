<?php

/**
 * Test: Nette\Collections\Set modifing iterator.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\Set;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



$set = new Set(NULL, 'Person');
$set->append(new Person('Jack'));
$set->append(new Person('Mary'));
$set->append(new Person('Larry'));

foreach ($set as & $person) {
	$person = 10;
}

T::dump( $set );



__halt_compiler() ?>

------EXPECT------
%ns%Set(
	"%h%" => Person(
		"name" private => "Jack"
	)
	"%h%" => Person(
		"name" private => "Mary"
	)
	"%h%" => Person(
		"name" private => "Larry"
	)
)
