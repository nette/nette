<?php

/**
 * Test: Nette\Reflection\AnnotationsParser using user classes.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 */

use Nette\Reflection;


require __DIR__ . '/../bootstrap.php';


class SecuredAnnotation extends Reflection\Annotation
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

$rc = new Reflection\ClassType('TestClass');
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
