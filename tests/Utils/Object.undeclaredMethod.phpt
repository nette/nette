<?php

/**
 * Test: Nette\Object undeclared method.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Object;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Object.inc';



try {
	$obj = new TestClass;
	$obj->undeclared();

	Assert::failed();
} catch (Exception $e) {
	Assert::exception('MemberAccessException', 'Call to undefined method TestClass::undeclared().', $e );
}
