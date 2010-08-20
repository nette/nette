<?php

/**
 * Test: Annotations using user classes.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\ClassReflection;



require __DIR__ . '/../initialize.php';



class SecuredAnnotation extends Nette\Reflection\Annotation
{
	public $role;
	public $level;
	public $value;
}


/**
 * @secured(disabled)
 */
class TestClass {

	/** @secured(role = "admin", level = 2) */
	public $foo;

}



// Class annotations

$rc = new ClassReflection('TestClass');
Assert::equal( array(
	'secured' => array(
		new SecuredAnnotation(array(
			'role' => NULL,
			'level' => NULL,
			'value' => 'disabled',
		)),
	),
), $rc->getAnnotations() );


Assert::equal( array(
	'secured' => array(
		new SecuredAnnotation(array(
			'role' => 'admin',
			'level' => 2,
			'value' => NULL,
		)),
	),
), $rc->getProperty('foo')->getAnnotations() );
