<?php

/**
 * Test: Nette\Templates\LatteMacros::formatArray()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';



new LatteMacros;

// symbols

Assert::same( '',  LatteMacros::formatArray('') );
Assert::same( '',  LatteMacros::formatArray('', '&') );
Assert::same( 'array(1)',  LatteMacros::formatArray('1') );
Assert::same( '&array(1)',  LatteMacros::formatArray('1', '&') );
Assert::same( "array('symbol')",  LatteMacros::formatArray('symbol') );
Assert::same( "array(1, 2, 'symbol1', 'symbol2')",  LatteMacros::formatArray('1, 2, symbol1, symbol2') );

// strings

Assert::same( 'array("\"1, 2, symbol1, symbol2")',  LatteMacros::formatArray('"\"1, 2, symbol1, symbol2"') ); // unable to parse "${'"'}" yet
Assert::same( "array('\\'1, 2, symbol1, symbol2')",  LatteMacros::formatArray("'\\'1, 2, symbol1, symbol2'") );
try {
	LatteMacros::formatArray("'\\\\'1, 2, symbol1, symbol2'");
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\TokenizerException', 'Unexpected %a% on line 1, column 27.', $e );
}

// key words

Assert::same( 'array(TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class)',  LatteMacros::formatArray('TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class') );
Assert::same( 'array(func (10))',  LatteMacros::formatArray('func (10)') );

// associative arrays

Assert::same( "array('symbol1' => 'value','symbol2'=>'value')",  LatteMacros::formatArray('symbol1 => value,symbol2=>value') );
Assert::same( "array('symbol1' => array ('symbol2' => 'value'))",  LatteMacros::formatArray('symbol1 => array (symbol2 => value)') );

// simplified arrays

Assert::same( 'array(array(\'item\', 123, array(), $item[1]))',  LatteMacros::formatArray('[item, 123, [], $item[1]]') );

// special

Assert::same( 'array($var)',  LatteMacros::formatArray('$var') );
Assert::same( 'array($var => $var)',  LatteMacros::formatArray('$var => $var') );
Assert::same( "array('symbol' => Class::CONST, )",  LatteMacros::formatArray('symbol => Class::CONST, ') );
Assert::same( "array('symbol' => Class::method(), )",  LatteMacros::formatArray('symbol => Class::method(), ') );
Assert::same( "array('symbol' => Namespace\\Class::method())",  LatteMacros::formatArray('symbol => Namespace\Class::method()') );
Assert::same( "array('symbol' => Namespace \\ Class :: method ())",  LatteMacros::formatArray('symbol => Namespace \ Class :: method ()') );
Assert::same( "array('symbol' => \$this->var, )",  LatteMacros::formatArray('symbol => $this->var, ') );
Assert::same( "array('symbol' => \$this -> var, )",  LatteMacros::formatArray('symbol => $this -> var, ') );
Assert::same( "array('symbol' => \$this -> var)",  LatteMacros::formatArray('symbol => $this -> var') );
Assert::same( "array('symbol1' => 'value')",  LatteMacros::formatArray('symbol1 => /*value,* /symbol2=>*/value/**/') );
