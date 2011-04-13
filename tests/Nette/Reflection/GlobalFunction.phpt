<?php

/**
 * Test: FunctionReflection tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



function bar() {}

$function = new Reflection\GlobalFunction('bar');
Assert::null( $function->getExtension() );


$function = new Reflection\GlobalFunction('sort');
Assert::equal( new Reflection\Extension('standard'), $function->getExtension() );
