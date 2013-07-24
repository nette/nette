<?php

/**
 * Test: Nette\Latte\Macros\UIMacros {ifset #block}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\Macros\UIMacros;


require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
UIMacros::install($compiler);

// {ifset ... }
Assert::same( '<?php if (isset($_l->blocks["block"])): ?>',  $compiler->expandMacro('ifset', '#block')->openingCode );
Assert::same( '<?php if (isset($item->var["#test"], $_l->blocks["block"])): ?>',  $compiler->expandMacro('ifset', '$item->var["#test"], #block')->openingCode );

Assert::exception(function() use ($compiler) {
	Assert::same( '<?php if (isset($var)): ?>',  $compiler->expandMacro('ifset', '$var')->openingCode );
}, 'Nette\Latte\CompileException', 'Unhandled macro {ifset}');
