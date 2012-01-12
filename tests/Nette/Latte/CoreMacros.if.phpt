<?php

/**
 * Test: Nette\Latte\Macros\CoreMacros: {if ...}
 *
 * @author     Matej Kravjar
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\Macros\CoreMacros;



require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
CoreMacros::install($compiler);
function item1($a) { return $a[1]; }

Assert::same( '<?php if (isset($var)): ?>',  item1($compiler->expandMacro('ifset', '$var')) );
Assert::same( '<?php if (isset($item->var["test"])): ?>',  item1($compiler->expandMacro('ifset', '$item->var["test"]')) );
