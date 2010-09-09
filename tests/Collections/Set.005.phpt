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

Assert::true( $set->contains($jack), "Contains Jack?" );

Assert::false( $set->contains($larry), "Contains Larry?" );

Assert::false( $set->contains($foo), "Contains foo?" );
