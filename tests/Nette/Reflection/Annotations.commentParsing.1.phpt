<?php

/**
 * Test: Annotations comment parser I.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



/**
 * This is my favorite class.
 * @title(value ="Johno's addendum", mode=True,) , out
 * @title( value= 'One, Two', mode= true or false)
 * @title( value = 'Three (Four)', mode = 'false')
 * @components(item 1)
 * @persistent(true)
 * @persistent(FALSE)
 * @persistent(null)
 * @author true
 * @author FALSE
 * @author null
 * @author
 * @author John Doe
 * @renderable
 */
class TestClass {

	/** @secured(role = "admin", level = 2) */
	public $foo;

	/** @RolesAllowed('admin', web editor) */
	public function foo()
	{}

}



// Class annotations

$rc = new Reflection\ClassType('TestClass');
$tmp = $rc->getAnnotations();

Assert::same( "This is my favorite class.",  $tmp['description'][0] );
Assert::same( "Johno's addendum",  $tmp['title'][0]->value );
Assert::true( $tmp['title'][0]->mode );
Assert::same( 'One, Two',  $tmp['title'][1]->value );
Assert::same( 'true or false',  $tmp['title'][1]->mode );
Assert::same( 'Three (Four)',  $tmp['title'][2]->value );
Assert::same( 'false',  $tmp['title'][2]->mode );
Assert::same( 'item 1',  $tmp['components'][0] );
Assert::true( $tmp['persistent'][0], 'persistent' );
Assert::false( $tmp['persistent'][1] );
Assert::null( $tmp['persistent'][2] );
Assert::true( $tmp['author'][0], 'author' );
Assert::false( $tmp['author'][1] );
Assert::null( $tmp['author'][2] );
Assert::true( $tmp['author'][3] );
Assert::same( 'John Doe',  $tmp['author'][4] );
Assert::true( $tmp['renderable'][0] );

Assert::true( $tmp === $rc->getAnnotations(), 'cache test' );
Assert::true( $tmp !== Reflection\ClassType::from('ReflectionClass')->getAnnotations(), 'cache test' );

Assert::true( $rc->hasAnnotation('title'), "has('title')' );
Assert::same( 'Three (Four)",  $rc->getAnnotation('title')->value );
Assert::same( 'false',  $rc->getAnnotation('title')->mode );

$tmp = $rc->getAnnotations('title');
/*
Assert::same( "Johno's addendum",  $tmp[0]->value );
Assert::true( $tmp[0]->mode );
Assert::same( 'One, Two',  $tmp[1]->value );
Assert::same( 'true or false',  $tmp[1]->mode );
Assert::same( 'Three (Four)',  $tmp[2]->value );
Assert::same( 'false',  $tmp[2]->mode );
*/

Assert::true( $rc->hasAnnotation('renderable'), "has('renderable')" );
Assert::true( $rc->getAnnotation('renderable'), "get('renderable')" );
/*
$tmp = $rc->getAnnotations('renderable');
Assert::true( $tmp[0] );
$tmp = $rc->getAnnotations('persistent');
*/
Assert::null( $rc->getAnnotation('persistent'), "get('persistent')" );
/*
Assert::true( $tmp[0] );
Assert::false( $tmp[1] );
Assert::null( $tmp[2] );
*/

Assert::false( $rc->hasAnnotation('xxx'), "has('xxx')" );
Assert::null( $rc->getAnnotation('xxx'), "get('xxx')" );


// Method annotations

$rm = $rc->getMethod('foo');
$tmp = $rm->getAnnotations();

Assert::same( 'admin',  $tmp['RolesAllowed'][0][0] );
Assert::same( 'web editor',  $tmp['RolesAllowed'][0][1] );


// Property annotations

$rp = $rc->getProperty('foo');
$tmp = $rp->getAnnotations();

Assert::same( 'admin',  $tmp['secured'][0]->role );
Assert::same( 2,  $tmp['secured'][0]->level );
