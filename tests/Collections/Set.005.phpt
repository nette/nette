<?php

/**
 * Test: Nette\Collections\Set::contains()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

/*use Nette\Collections\Set;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';



$set = new Set(NULL, 'Person');
$set->append($jack = new Person('Jack'));
$set->append(new Person('Mary'));
$larry = new Person('Larry');
$foo = new ArrayObject;

dump( $set->contains($jack), "Contains Jack?" );

dump( $set->contains($larry), "Contains Larry?" );

try {
	dump( $set->contains($foo), "Contains foo?" );
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
Contains Jack? bool(TRUE)

Contains Larry? bool(FALSE)

Contains foo? bool(FALSE)
