<?php

/**
 * Test: Nette\Latte\PhpWriter::formatArray()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\PhpWriter,
	Nette\Latte\MacroTokenizer;



require __DIR__ . '/../bootstrap.php';



function formatArray($args, $prefix = NULL) {
	$writer = new PhpWriter(new MacroTokenizer($args));
	return $writer->formatArray($prefix);
}


// symbols
Assert::same( '',  formatArray('') );
Assert::same( '',  formatArray('', '&') );
Assert::same( 'array(1)',  formatArray('1') );
Assert::same( '&array(1)',  formatArray('1', '&') );
Assert::same( "array('symbol')",  formatArray('symbol') );
Assert::same( "array(1, 2, 'symbol1', 'symbol-2')",  formatArray('1, 2, symbol1, symbol-2') );


// simplified arrays
Assert::same( 'array(array(\'item\', 123, array(), $item[1]))',  formatArray('[item, 123, [], $item[1]]') );


// expand
Assert::same( 'array_merge(array(\'item\', $list, ), $list, array())',  formatArray('item, $list, (expand) $list') );
