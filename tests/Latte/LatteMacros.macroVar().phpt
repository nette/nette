<?php

/**
 * Test: Nette\Templates\LatteMacros::macroVar() & macroDefault()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';


$macros = new LatteMacros;

// {var ... }
Assert::same( '$var = \'hello\'',  $macros->macroVar('var => hello', '') );
Assert::same( '$var = 123',  $macros->macroVar('$var => 123', '') );
Assert::same( '$var = 123',  $macros->macroVar('$var = 123', '') );
Assert::same( '$var = 123',  $macros->macroVar('$var => 123', 'filter') );
Assert::same( '$var1 = 123; $var2 = "nette framework"',  $macros->macroVar('var1 = 123, $var2 => "nette framework"', '') );

try {
	$macros->macroVar('$var => "123', '');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\TokenizerException', 'Unexpected %a% on line 1, column 9.', $e );
}


// {default ...}
Assert::same( "extract(array('var' => 'hello'), EXTR_SKIP)",  $macros->macroDefault('var => hello', '') );
Assert::same( "extract(array('var' => 123), EXTR_SKIP)",  $macros->macroDefault('$var => 123', '') );
Assert::same( "extract(array('var' => 123), EXTR_SKIP)",  $macros->macroDefault('$var = 123', '') );
Assert::same( "extract(array('var' => 123), EXTR_SKIP)",  $macros->macroDefault('$var => 123', 'filter') );
Assert::same( "extract(array('var1' => 123, 'var2' => \"nette framework\"), EXTR_SKIP)",  $macros->macroDefault('var1 = 123, $var2 => "nette framework"', '') );
