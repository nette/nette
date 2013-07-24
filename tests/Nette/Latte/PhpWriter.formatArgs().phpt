<?php

/**
 * Test: Nette\Latte\PhpWriter::formatArgs()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\PhpWriter,
	Nette\Latte\MacroTokens;


require __DIR__ . '/../bootstrap.php';


function formatArgs($args) {
	$writer = new PhpWriter(new MacroTokens($args));
	return $writer->formatArgs();
}


test(function() { // symbols
	Assert::same( '',  formatArgs('') );
	Assert::same( '1',  formatArgs('1') );
	Assert::same( "'symbol'",  formatArgs('symbol') );
	Assert::same( "1, 2, 'symbol1', 'symbol-2'",  formatArgs('1, 2, symbol1, symbol-2') );
	Assert::same( "('a', 'b', 'c' => 'd', 'e' ? 'f' : 'g', h['i'], j('k'))",  formatArgs('(a, b, c => d, e ? f : g, h[i], j(k))') );
	Assert::same( "'x' && 'y', 'x' || 'y', 'x' < 'y', 'x' <= 'y', 'x' > 'y', 'x' => 'y', 'x' == 'y', 'x' === 'y', 'x' != 'y', 'x' !== 'y', 'x' <> 'y'",  formatArgs('x && y, x || y, x < y, x <= y, x > y, x => y, x == y, x === y, x != y, x !== y, x <> y') );
	Assert::same( "\$x = 'x', x = 1, 'x' . 'y'",  formatArgs('$x = x, x = 1, x . y') ); //
});


test(function() { // strings
	Assert::same( '"\"1, 2, symbol1, symbol2"',  formatArgs('"\"1, 2, symbol1, symbol2"') ); // unable to parse "${'"'}" yet
	Assert::same( "'\\'1, 2, symbol1, symbol2'",  formatArgs("'\\'1, 2, symbol1, symbol2'") );
	Assert::same( "('hello')",  formatArgs('(hello)') );
	Assert::exception(function() {
		formatArgs("'\\\\'1, 2, symbol1, symbol2'");
	}, 'Nette\Utils\TokenizerException', 'Unexpected %a% on line 1, column 27.');
});


test(function() { // key words
	Assert::same( 'TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class',  formatArgs('TRUE, false, null, 1 or 1 and 2 xor 3, clone $obj, new Class') );
	Assert::same( 'func (10)',  formatArgs('func (10)') );
});


test(function() { // associative arrays
	Assert::same( "'symbol1' => 'value','symbol2'=>'value'",  formatArgs('symbol1 => value,symbol2=>value') );
	Assert::same( "'symbol1' => array ('symbol2' => 'value')",  formatArgs('symbol1 => array (symbol2 => value)') );
});


test(function() { // simplified arrays
	Assert::same( 'array(\'item\', 123, array(), $item[1])',  formatArgs('[item, 123, [], $item[1]]') );
	Assert::same( "ITEM['id']",  formatArgs('ITEM[id]') );
});


test(function() { // short ternary operators
	Assert::same( "(\$first ? 'first' : NULL), \$var ? 'foo' : 'bar', \$var ? 'foo' : NULL",  formatArgs('($first ? first), $var ? foo : bar, $var ? foo') );
	Assert::same( "('a' ? 'b' : NULL) ? ('c' ? 'd' : NULL) : NULL",  formatArgs('(a ? b) ? (c ? d)') );
});


test(function() { // special
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
	Assert::same( "(array)",  formatArgs('(array)') );
	Assert::same( 'func()[1]',  formatArgs('func()[1]') );
});


test(function() { // special UTF-8
	Assert::same( "'Iñtërnâtiônàlizætiøn' => 'Iñtërnâtiônàlizætiøn'",  formatArgs('Iñtërnâtiônàlizætiøn => Iñtërnâtiônàlizætiøn') );
	Assert::same( '$våŕìăbłë',  formatArgs('$våŕìăbłë') );
	Assert::same( "'M_PIÁNO'",  formatArgs('M_PIÁNO') );
	Assert::same( "'symbôl-1' => 'vålue-2'",  formatArgs('symbôl-1 => vålue-2') );
});
