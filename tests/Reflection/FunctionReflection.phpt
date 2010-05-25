<?php

/**
 * Test: FunctionReflection tests.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

/*use Nette\Reflection\FunctionReflection;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



function bar() {}

$function = new FunctionReflection('bar');
dump( $function->getExtension() );

$function = new FunctionReflection('sort');
dump( $function->getExtension() );



__halt_compiler() ?>

------EXPECT------
NULL

object(%ns%ExtensionReflection) (1) {
	"name" => string(8) "standard"
}
