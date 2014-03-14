<?php

/**
 * Test: Nette\Latte\Macros\CoreMacros: {if ...}
 *
 * @author     Matej Kravjar
 */

use Nette\Latte\Macros\CoreMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
CoreMacros::install($compiler);

Assert::same( '<?php if (isset($var)) { ?>',  $compiler->expandMacro('ifset', '$var')->openingCode );
Assert::same( '<?php if (isset($item->var["test"])) { ?>',  $compiler->expandMacro('ifset', '$item->var["test"]')->openingCode );
