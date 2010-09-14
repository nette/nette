<?php

/**
 * Test: ParameterReflection tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 * @phpversion 5.3
 */

use Nette\Reflection\ClassReflection,
	Nette\Reflection\FunctionReflection;



require __DIR__ . '/../initialize.php';



$reflect = new FunctionReflection(function ($x, $y) {});
$params = $reflect->getParameters();
Assert::same( 2, count($params) );
Assert::same( 'Function {closure}()', (string) $params[0]->declaringFunction );
Assert::null( $params[0]->class );
Assert::null( $params[0]->declaringClass );
Assert::same( 'Function {closure}()', (string) $params[1]->declaringFunction );
Assert::null( $params[1]->class );
Assert::null( $params[1]->declaringClass );
