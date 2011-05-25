<?php

/**
 * Test: Nette\Latte\DefaultMacros::macroIfset()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\DefaultMacros;



require __DIR__ . '/../bootstrap.php';


$parser = new Nette\Latte\Parser;
DefaultMacros::install($parser);
function item1($a) { return $a[1]; }

$prefix = '<?php foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator(';

// {ifset ... }
Assert::same( '<?php if (isset($var)): ?>',  item1($parser->expandMacro('ifset', '$var')) );
Assert::same( '<?php if (isset($item->var["test"])): ?>',  item1($parser->expandMacro('ifset', '$item->var["test"]')) );
Assert::same( '<?php if (isset($_l->blocks["block"])): ?>',  item1($parser->expandMacro('ifset', '#block')) );
Assert::same( '<?php if (isset($item->var["#test"], $_l->blocks["block"])): ?>',  item1($parser->expandMacro('ifset', '$item->var["#test"], #block')) );
