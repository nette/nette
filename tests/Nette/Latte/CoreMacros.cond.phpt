<?php

/**
 * Test: Nette\Latte\Macros\CoreMacros.
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

$prefix = '<?php foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator(';

Assert::same( '<?php if (isset($var)): ?>',  item1($parser->expandMacro('ifset', '$var')) );
Assert::same( '<?php if (isset($item->var["test"])): ?>',  item1($parser->expandMacro('ifset', '$item->var["test"]')) );
