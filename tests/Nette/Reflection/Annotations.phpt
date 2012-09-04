<?php

/**
 * Test: Nette\Reflection & annotations.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



/**
 * @author John Doe
 * @renderable
 */
class TestClass
{

	/** @secured */
	public $foo;

	/** @AJAX */
	public function foo()
	{}

}



/**
 * @internal
 * @param TestClass $bla
 */
function bar(TestClass $bla)
{}



// Class annotations

$rc = new Reflection\ClassType('TestClass');
$tmp = $rc->getAnnotations();

Assert::same( 'John Doe',  $tmp['author'][0] );
Assert::true( $tmp['renderable'][0] );

Assert::true( $rc->hasAnnotation('author'), "has('author')' );
Assert::same( 'John Doe",  $rc->getAnnotation('author') );



// Method annotations

$rm = $rc->getMethod('foo');
$tmp = $rm->getAnnotations();

Assert::true( $tmp['AJAX'][0] );
Assert::true( $rm->hasAnnotation('AJAX'), "has('AJAX')" );
Assert::true( $rm->getAnnotation('AJAX') );


// Property annotations

$rp = $rc->getProperty('foo');
$tmp = $rp->getAnnotations();

Assert::true( $tmp['secured'][0] );
Assert::true( $rp->hasAnnotation('secured'), "has('secured')" );
Assert::true( $rp->getAnnotation('secured') );



// Function annotations

$rf = new Reflection\GlobalFunction('bar');
$tmp = $rf->getAnnotations();

Assert::true($tmp['internal'][0]);
Assert::true($rf->hasAnnotation('internal'), "has('internal')");
Assert::true($rf->getAnnotation('internal'));
