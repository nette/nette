<?php

/**
 * Test: Nette\Latte\Macros\UIMacros: {link ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\Macros\UIMacros;



require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
$compiler->setContext(Nette\Latte\Compiler::CONTEXT_NONE);
UIMacros::install($compiler);
function item1($a) { return $a[1]; }

// {link ...}
Assert::same( '<?php echo $_control->link("p") ?>',  item1($compiler->expandMacro('link', 'p', '')) );
/*Assert::same( '<?php echo ($template->filter$_control->link("p")) ?>',  item1($compiler->expandMacro('link', 'p', 'filter')) );*/
Assert::same( '<?php echo $_control->link("p:a") ?>',  item1($compiler->expandMacro('link', 'p:a', '')) );
Assert::same( '<?php echo $_control->link($dest) ?>',  item1($compiler->expandMacro('link', '$dest', '')) );
Assert::same( '<?php echo $_control->link($p:$a) ?>',  item1($compiler->expandMacro('link', '$p:$a', '')) );
Assert::same( '<?php echo $_control->link("$p:$a") ?>',  item1($compiler->expandMacro('link', '"$p:$a"', '')) );
Assert::same( '<?php echo $_control->link("p:a") ?>',  item1($compiler->expandMacro('link', '"p:a"', '')) );
Assert::same( '<?php echo $_control->link(\'p:a\') ?>',  item1($compiler->expandMacro('link', "'p:a'", '')) );

Assert::same( '<?php echo $_control->link("p", array(\'param\')) ?>',  item1($compiler->expandMacro('link', 'p param', '')) );
Assert::same( '<?php echo $_control->link("p", array(\'param\' => 123)) ?>',  item1($compiler->expandMacro('link', 'p param => 123', '')) );
Assert::same( '<?php echo $_control->link("p", array(\'param\' => 123)) ?>',  item1($compiler->expandMacro('link', 'p, param => 123', '')) );
