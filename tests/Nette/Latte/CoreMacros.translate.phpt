<?php

/**
 * Test: Latte\Macros\CoreMacros: {_translate}
 *
 * @author     David Grudl
 */

use Latte\Macros\CoreMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$compiler = new Latte\Compiler;
CoreMacros::install($compiler);

// {_...}
Assert::same( '<?php echo $template->escape($template->translate(\'var\')) ?>',  $compiler->expandMacro('_', 'var', '')->openingCode );
Assert::same( '<?php echo $template->escape($template->filter($template->translate(\'var\'))) ?>',  $compiler->expandMacro('_', 'var', '|filter')->openingCode );
