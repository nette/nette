<?php

/**
 * Test: Nette\Latte\Macros\CoreMacros::macroForeach()
 *
 * @author     Matej Kravjar
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\Macros\CoreMacros;



require __DIR__ . '/../bootstrap.php';


$parser = new Nette\Latte\Parser;
CoreMacros::install($parser);
function item1($a) { return $a[1]; }

$prefix = '<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator(';

Assert::same( $prefix . '$array) as $value): ?>',  item1($parser->expandMacro('foreach', '$array as $value')) );
Assert::same( $prefix . '$array) as $key => $value): ?>',  item1($parser->expandMacro('foreach', '$array as $key => $value')) );

Assert::same( $prefix . '$obj->data("A as B")) as $value): ?>',  item1($parser->expandMacro('foreach', '$obj->data("A as B") as $value')) );
Assert::same( $prefix . '$obj->data(\'A as B\')) as $value): ?>',  item1($parser->expandMacro('foreach', '$obj->data(\'A as B\') as $value')) );
Assert::same( $prefix . '$obj->data("X as Y, Z as W")) as $value): ?>',  item1($parser->expandMacro('foreach', '$obj->data("X as Y, Z as W") as $value')) );
