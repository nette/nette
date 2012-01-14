<?php

/**
 * Test: Nette\Latte\Macros\CoreMacros: {foreach ...}
 *
 * @author     Matej Kravjar
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\Macros\CoreMacros;



require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
CoreMacros::install($compiler);

$prefix = '<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator(';

Assert::same( $prefix . '$array) as $value): ?>',  $compiler->expandMacro('foreach', '$array as $value')->openingCode );
Assert::same( $prefix . '$array) as $key => $value): ?>',  $compiler->expandMacro('foreach', '$array as $key => $value')->openingCode );

Assert::same( $prefix . '$obj->data("A as B")) as $value): ?>',  $compiler->expandMacro('foreach', '$obj->data("A as B") as $value')->openingCode );
Assert::same( $prefix . '$obj->data(\'A as B\')) as $value): ?>',  $compiler->expandMacro('foreach', '$obj->data(\'A as B\') as $value')->openingCode );
Assert::same( $prefix . '$obj->data("X as Y, Z as W")) as $value): ?>',  $compiler->expandMacro('foreach', '$obj->data("X as Y, Z as W") as $value')->openingCode );
