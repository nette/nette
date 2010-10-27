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



$latte = new LatteMacros;

// symbols

Assert::same( '',  $latte->formatArray('') );
Assert::same( '',  $latte->formatArray('', '&') );
Assert::same( 'array(1)',  $latte->formatArray('1') );
Assert::same( '&array(1)',  $latte->formatArray('1', '&') );
Assert::same( "array('symbol')",  $latte->formatArray('symbol') );
Assert::same( "array(1, 2, 'symbol1', 'symbol-2')",  $latte->formatArray('1, 2, symbol1, symbol-2') );

// strings

Assert::same( 'array("\"1, 2, symbol1, symbol2")',  $latte->formatArray('"\"1, 2, symbol1, symbol2"') ); // unable to parse "${'"'}" yet
Assert::same( "array('\\'1, 2, symbol1, symbol2')",  $latte->formatArray("'\\'1, 2, symbol1, symbol2'") );
try {
	$latte->formatArray("'\\\\'1, 2, symbol1, symbol2'");
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\TokenizerException', 'Unexpected %a% on line 1, column 27.', $e );
}

// key words

Assert::same( 'array(TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class)',  $latte->formatArray('TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class') );
Assert::same( 'array(func (10))',  $latte->formatArray('func (10)') );

// associative arrays

Assert::same( "array('symbol1' => 'value','symbol2'=>'value')",  $latte->formatArray('symbol1 => value,symbol2=>value') );
Assert::same( "array('symbol1' => array ('symbol2' => 'value'))",  $latte->formatArray('symbol1 => array (symbol2 => value)') );

// simplified arrays

Assert::same( 'array(array(\'item\', 123, array(), $item[1]))',  $latte->formatArray('[item, 123, [], $item[1]]') );

// short ternary operators

Assert::same( 'array(($first ? \'first\':null), $var ? \'foo\' : \'bar\', $var ? \'foo\':null)',  $latte->formatArray('($first ? first), $var ? foo : bar, $var ? foo') );

// expand

Assert::same( 'array_merge(array(\'item\', $list, ), $list, array())',  $latte->formatArray('item, $list, (expand) $list') );

// special

Assert::same( 'array($var)',  $latte->formatArray('$var') );
Assert::same( 'array($var => $var)',  $latte->formatArray('$var => $var') );
Assert::same( "array('symbol' => Class::CONST, )",  $latte->formatArray('symbol => Class::CONST, ') );
Assert::same( "array('symbol' => Class::method(), )",  $latte->formatArray('symbol => Class::method(), ') );
Assert::same( "array('symbol' => Namespace\\Class::method())",  $latte->formatArray('symbol => Namespace\Class::method()') );
Assert::same( "array('symbol' => Namespace \\ Class :: method ())",  $latte->formatArray('symbol => Namespace \ Class :: method ()') );
Assert::same( "array('symbol' => \$this->var, )",  $latte->formatArray('symbol => $this->var, ') );
Assert::same( "array('symbol' => \$this -> var, )",  $latte->formatArray('symbol => $this -> var, ') );
Assert::same( "array('symbol' => \$this -> var)",  $latte->formatArray('symbol => $this -> var') );
Assert::same( "array('symbol1' => 'value')",  $latte->formatArray('symbol1 => /*value,* /symbol2=>*/value/**/') );
