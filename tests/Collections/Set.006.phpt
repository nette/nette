<?php

/**
 * Test: Nette\Collections\Set and removing items.
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
$set->append($larry = new Person('Larry'));
$foo = new ArrayObject;

T::dump( $set->remove($larry), "Removing Larry" );

T::dump( $set->remove($larry), "Removing Larry second time" );

try {
	T::dump( $set->remove($foo), "Removing foo" );
} catch (Exception $e) {
	T::dump( $e );
}

T::dump( $set );



__halt_compiler() ?>

------EXPECT------
Removing Larry: TRUE

Removing Larry second time: FALSE

Removing foo: FALSE

%ns%Set(
	"%h%" => Person(
		"name" private => "Jack"
	)
	"%h%" => Person(
		"name" private => "Mary"
	)
)
