<?php

/**
 * Test: Nette\Latte\Macros\CoreMacros::macroTranslate()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\Macros\CoreMacros;



require __DIR__ . '/../bootstrap.php';


$parser = new Nette\Latte\Parser;
CoreMacros::install($parser);
function item1($a) { return $a[1]; }

// {_...}
Assert::same( '<?php echo $template->translate(\'var\') ?>',  item1($parser->expandMacro('_', 'var', '')) );
Assert::same( '<?php echo $template->filter($template->translate(\'var\')) ?>',  item1($parser->expandMacro('_', 'var', '|filter')) );
