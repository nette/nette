<?php

/**
 * Test: Nette\Latte\DefaultMacros::macroTranslate()
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

// {_...}
Assert::same( '<?php echo $template->translate(\'var\') ?>',  item1($parser->expandMacro('_', 'var', '')) );
Assert::same( '<?php echo $template->filter($template->translate(\'var\')) ?>',  item1($parser->expandMacro('_', 'var', '|filter')) );
