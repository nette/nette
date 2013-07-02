<?php

/**
 * Test: Nette\Latte\Macros\CoreMacros: {foreach ...}
 *
 * @author     Matej Kravjar
 * @package    Nette\Latte
 */

use Nette\Latte\Macros\CoreMacros;


require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
CoreMacros::install($compiler);

$prefix = '<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator(';

function expandMacro($compiler, $args) {
	$node = $compiler->expandMacro('foreach', $args);
	$node->content = ' $iterator ';
	$node->closing = TRUE;
	$node->macro->nodeClosed($node);
	return $node;
}

Assert::same( $prefix . '$array) as $value): ?>',  expandMacro($compiler, '$array as $value')->openingCode );
Assert::same( $prefix . '$array) as $key => $value): ?>',  expandMacro($compiler, '$array as $key => $value')->openingCode );

Assert::same( $prefix . '$obj->data("A as B")) as $value): ?>',  expandMacro($compiler, '$obj->data("A as B") as $value')->openingCode );
Assert::same( $prefix . '$obj->data(\'A as B\')) as $value): ?>',  expandMacro($compiler, '$obj->data(\'A as B\') as $value')->openingCode );
Assert::same( $prefix . '$obj->data("X as Y, Z as W")) as $value): ?>',  expandMacro($compiler, '$obj->data("X as Y, Z as W") as $value')->openingCode );
