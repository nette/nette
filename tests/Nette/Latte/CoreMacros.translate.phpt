<?php

/**
 * Test: Nette\Latte\Macros\CoreMacros: {_translate}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\Macros\CoreMacros;


require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
CoreMacros::install($compiler);

// {_...}
Assert::same( '<?php echo $template->escape($template->translate(\'var\')) ?>',  $compiler->expandMacro('_', 'var', '')->openingCode );
Assert::same( '<?php echo $template->escape($template->filter($template->translate(\'var\'))) ?>',  $compiler->expandMacro('_', 'var', '|filter')->openingCode );
