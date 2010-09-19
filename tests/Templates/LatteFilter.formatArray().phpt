<?php

/**
 * Test: Nette\Templates\LatteFilter::formatArray()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteFilter;



require __DIR__ . '/../bootstrap.php';



// symbols

Assert::same( '',  LatteFilter::formatArray('') );
Assert::same( '',  LatteFilter::formatArray('', '&') );
Assert::same( 'array(1)',  LatteFilter::formatArray('1') );
Assert::same( '&array(1)',  LatteFilter::formatArray('1', '&') );
Assert::same( "array('symbol')",  LatteFilter::formatArray('symbol') );
Assert::same( "array(1, 2,'symbol1','symbol2')",  LatteFilter::formatArray('1, 2, symbol1, symbol2') );

// strings

Assert::same( 'array("\"1, 2, symbol1, symbol2")',  LatteFilter::formatArray('"\"1, 2, symbol1, symbol2"') ); // unable to parse "${'"'}" yet
Assert::same( "array('\\'1, 2, symbol1, symbol2')",  LatteFilter::formatArray("'\\'1, 2, symbol1, symbol2'") );
Assert::same( "array('\\\\'1, 2,'symbol1', symbol2')",  LatteFilter::formatArray("'\\\\'1, 2, symbol1, symbol2'") );

// key words

Assert::same( 'array(TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class)',  LatteFilter::formatArray('TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class') );
Assert::same( 'array(func (10))',  LatteFilter::formatArray('func (10)') );

// associative arrays

Assert::same( "array('symbol1' =>'value','symbol2'=>'value')",  LatteFilter::formatArray('symbol1 => value,symbol2=>value') );
Assert::same( "array('symbol1' => array ('symbol2' =>'value'))",  LatteFilter::formatArray('symbol1 => array (symbol2 => value)') );

// special

Assert::same( 'array($var)',  LatteFilter::formatArray('$var') );
Assert::same( 'array("var" => $var)',  LatteFilter::formatArray('$var => $var') );
Assert::same( "array('symbol' => Class::CONST,)",  LatteFilter::formatArray('symbol => Class::CONST, ') );
Assert::same( "array('symbol' => Class::method(),)",  LatteFilter::formatArray('symbol => Class::method(), ') );
Assert::same( "array('symbol' => Namespace\\Class::method())",  LatteFilter::formatArray('symbol => Namespace\Class::method()') );
Assert::same( "array('symbol' => Namespace \\ Class :: method ())",  LatteFilter::formatArray('symbol => Namespace \ Class :: method ()') );
Assert::same( "array('symbol' => \$this->var,)",  LatteFilter::formatArray('symbol => $this->var, ') );
Assert::same( "array('symbol' => \$this -> var,)",  LatteFilter::formatArray('symbol => $this -> var, ') );
Assert::same( "array('symbol' => \$this -> var)",  LatteFilter::formatArray('symbol => $this -> var') );
