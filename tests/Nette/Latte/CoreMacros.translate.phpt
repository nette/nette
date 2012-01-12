<?php

/**
 * Test: Nette\Latte\Macros\CoreMacros: {_translate}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\Macros\CoreMacros;



require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
CoreMacros::install($compiler);
function item1($a) { return $a[1]; }

// {_...}
Assert::same( '<?php echo $template->translate(\'var\') ?>',  item1($compiler->expandMacro('_', 'var', '')) );
Assert::same( '<?php echo $template->filter($template->translate(\'var\')) ?>',  item1($compiler->expandMacro('_', 'var', '|filter')) );
