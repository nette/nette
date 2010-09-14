<?php

/**
 * Test: FunctionReflection tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\FunctionReflection;



require __DIR__ . '/../initialize.php';



function bar() {}

$function = new FunctionReflection('bar');
Assert::null( $function->getExtension() );


$function = new FunctionReflection('sort');
Assert::equal( new Nette\Reflection\ExtensionReflection('standard'), $function->getExtension() );
