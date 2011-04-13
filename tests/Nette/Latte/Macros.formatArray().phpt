<?php

/**
 * Test: Nette\Latte\DefaultMacros::formatArray()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\DefaultMacros;



require __DIR__ . '/../bootstrap.php';



$latte = new DefaultMacros;

// symbols

Assert::same( '',  $latte->formatArray('') );
Assert::same( '',  $latte->formatArray('', '&') );
Assert::same( 'array(1)',  $latte->formatArray('1') );
Assert::same( '&array(1)',  $latte->formatArray('1', '&') );
Assert::same( "array('symbol')",  $latte->formatArray('symbol') );
Assert::same( "array(1, 2, 'symbol1', 'symbol-2')",  $latte->formatArray('1, 2, symbol1, symbol-2') );

// simplified arrays

Assert::same( 'array(array(\'item\', 123, array(), $item[1]))',  $latte->formatArray('[item, 123, [], $item[1]]') );

// expand

Assert::same( 'array_merge(array(\'item\', $list, ), $list, array())',  $latte->formatArray('item, $list, (expand) $list') );
