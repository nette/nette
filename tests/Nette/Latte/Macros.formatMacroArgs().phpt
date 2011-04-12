<?php

/**
 * Test: Nette\Templates\LatteMacros::formatMacroArgs()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';



$latte = new LatteMacros;

// symbols

Assert::same( '',  $latte->formatMacroArgs('') );
Assert::same( '1',  $latte->formatMacroArgs('1') );
Assert::same( "'symbol'",  $latte->formatMacroArgs('symbol') );
Assert::same( "1, 2, 'symbol1', 'symbol-2'",  $latte->formatMacroArgs('1, 2, symbol1, symbol-2') );

// strings

Assert::same( '"\"1, 2, symbol1, symbol2"',  $latte->formatMacroArgs('"\"1, 2, symbol1, symbol2"') ); // unable to parse "${'"'}" yet
Assert::same( "'\\'1, 2, symbol1, symbol2'",  $latte->formatMacroArgs("'\\'1, 2, symbol1, symbol2'") );
try {
	$latte->formatMacroArgs("'\\\\'1, 2, symbol1, symbol2'");
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\TokenizerException', 'Unexpected %a% on line 1, column 27.', $e );
}

// key words

Assert::same( 'TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class',  $latte->formatMacroArgs('TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class') );
Assert::same( 'func (10)',  $latte->formatMacroArgs('func (10)') );

// associative arrays

Assert::same( "'symbol1' => 'value','symbol2'=>'value'",  $latte->formatMacroArgs('symbol1 => value,symbol2=>value') );
Assert::same( "'symbol1' => array ('symbol2' => 'value')",  $latte->formatMacroArgs('symbol1 => array (symbol2 => value)') );

// simplified arrays

Assert::same( 'array(\'item\', 123, array(), $item[1])',  $latte->formatMacroArgs('[item, 123, [], $item[1]]') );

// short ternary operators

Assert::same( '($first ? \'first\':null), $var ? \'foo\' : \'bar\', $var ? \'foo\':null',  $latte->formatMacroArgs('($first ? first), $var ? foo : bar, $var ? foo') );

// special

Assert::same( '$var',  $latte->formatMacroArgs('$var') );
Assert::same( '$var => $var',  $latte->formatMacroArgs('$var => $var') );
Assert::same( "'Iñtërnâtiônàlizætiøn' => 'Iñtërnâtiônàlizætiøn'",  $latte->formatMacroArgs('Iñtërnâtiônàlizætiøn => Iñtërnâtiônàlizætiøn') );
Assert::same( "'truex' => 0word, 0true, true-true, true-1",  $latte->formatMacroArgs('truex => 0word, 0true, true-true, true-1') );
Assert::same( "'symbol' => CONST, M_PI ",  $latte->formatMacroArgs('symbol => CONST, M_PI ') );
Assert::same( "'symbol' => Class::CONST, ",  $latte->formatMacroArgs('symbol => Class::CONST, ') );
Assert::same( "'symbol' => Class::method(), ",  $latte->formatMacroArgs('symbol => Class::method(), ') );
Assert::same( "'symbol' => Namespace\\Class::method()",  $latte->formatMacroArgs('symbol => Namespace\Class::method()') );
Assert::same( "'symbol' => Namespace \\ Class :: method ()",  $latte->formatMacroArgs('symbol => Namespace \ Class :: method ()') );
Assert::same( "'symbol' => \$this->var, ",  $latte->formatMacroArgs('symbol => $this->var, ') );
Assert::same( "'symbol' => \$this -> var, ",  $latte->formatMacroArgs('symbol => $this -> var, ') );
Assert::same( "'symbol' => \$this -> var",  $latte->formatMacroArgs('symbol => $this -> var') );
Assert::same( "'symbol1' => 'value'",  $latte->formatMacroArgs('symbol1 => /*value,* /symbol2=>*/value/**/') );
