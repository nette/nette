<?php

/**
 * Test: Nette\Collections\Set::contains()
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
$set->append($jack = new Person('Jack'));
$set->append(new Person('Mary'));
$larry = new Person('Larry');
$foo = new ArrayObject;

T::dump( $set->contains($jack), "Contains Jack?" );

T::dump( $set->contains($larry), "Contains Larry?" );

try {
	T::dump( $set->contains($foo), "Contains foo?" );
} catch (Exception $e) {
	T::dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Contains Jack? TRUE

Contains Larry? FALSE

Contains foo? FALSE
