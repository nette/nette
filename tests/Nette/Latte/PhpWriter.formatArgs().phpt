<?php

/**
 * Test: Nette\Latte\PhpWriter::formatArgs()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\PhpWriter,
	Nette\Latte\MacroTokenizer;



require __DIR__ . '/../bootstrap.php';



function formatArgs($args) {
	$writer = new PhpWriter(new MacroTokenizer($args));
	return $writer->formatArgs();
}


// symbols
Assert::same( '',  formatArgs('') );
Assert::same( '1',  formatArgs('1') );
Assert::same( "'symbol'",  formatArgs('symbol') );
Assert::same( "1, 2, 'symbol1', 'symbol-2'",  formatArgs('1, 2, symbol1, symbol-2') );


// strings
Assert::same( '"\"1, 2, symbol1, symbol2"',  formatArgs('"\"1, 2, symbol1, symbol2"') ); // unable to parse "${'"'}" yet
Assert::same( "'\\'1, 2, symbol1, symbol2'",  formatArgs("'\\'1, 2, symbol1, symbol2'") );
Assert::throws(function() {
	formatArgs("'\\\\'1, 2, symbol1, symbol2'");
}, 'Nette\Utils\TokenizerException', 'Unexpected %a% on line 1, column 27.');


// key words
Assert::same( 'TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class',  formatArgs('TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class') );
Assert::same( 'func (10)',  formatArgs('func (10)') );


// associative arrays
Assert::same( "'symbol1' => 'value','symbol2'=>'value'",  formatArgs('symbol1 => value,symbol2=>value') );
Assert::same( "'symbol1' => array ('symbol2' => 'value')",  formatArgs('symbol1 => array (symbol2 => value)') );


// simplified arrays
Assert::same( 'array(\'item\', 123, array(), $item[1])',  formatArgs('[item, 123, [], $item[1]]') );


// short ternary operators
Assert::same( '($first ? \'first\':null), $var ? \'foo\' : \'bar\', $var ? \'foo\':null',  formatArgs('($first ? first), $var ? foo : bar, $var ? foo') );


// special
Assert::same( '$var',  formatArgs('$var') );
Assert::same( '$var => $var',  formatArgs('$var => $var') );
Assert::same( "'truex' => 0word, 0true, true-true, true-1",  formatArgs('truex => 0word, 0true, true-true, true-1') );
Assert::same( "'symbol' => 'PI'",  formatArgs('symbol => PI') );
Assert::same( "'symbol' => CONST, M_PI ",  formatArgs('symbol => CONST, M_PI ') );
Assert::same( "'symbol' => Class::CONST, ",  formatArgs('symbol => Class::CONST, ') );
Assert::same( "'symbol' => Class::method(), ",  formatArgs('symbol => Class::method(), ') );
Assert::same( "'symbol' => Namespace\\Class::method()",  formatArgs('symbol => Namespace\Class::method()') );
Assert::same( "'symbol' => Namespace \\ Class :: method ()",  formatArgs('symbol => Namespace \ Class :: method ()') );
Assert::same( "'symbol' => \$this->var, ",  formatArgs('symbol => $this->var, ') );
Assert::same( "'symbol' => \$this -> var, ",  formatArgs('symbol => $this -> var, ') );
Assert::same( "'symbol' => \$this -> var",  formatArgs('symbol => $this -> var') );
Assert::same( "'symbol1' => 'value'",  formatArgs('symbol1 => /*value,* /symbol2=>*/value/**/') );


// special UTF-8
Assert::same( "'Iñtërnâtiônàlizætiøn' => 'Iñtërnâtiônàlizætiøn'",  formatArgs('Iñtërnâtiônàlizætiøn => Iñtërnâtiônàlizætiøn') );
Assert::same( '$våŕìăbłë',  formatArgs('$våŕìăbłë') );
Assert::same( "'M_PIÁNO'",  formatArgs('M_PIÁNO') );
Assert::same( "'symbôl-1' => 'vålue-2'",  formatArgs('symbôl-1 => vålue-2') );
