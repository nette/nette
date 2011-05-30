<?php

/**
 * Test: Nette\Latte\Macros\UIMacros::macroLink()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\Macros\UIMacros;



require __DIR__ . '/../bootstrap.php';


$parser = new Nette\Latte\Parser;
UIMacros::install($parser);
function item1($a) { return $a[1]; }

// {link ...}
Assert::same( '<?php echo $control->link("p") ?>',  item1($parser->expandMacro('link', 'p', '')) );
/*Assert::same( '<?php echo ($template->filter$control->link("p")) ?>',  item1($parser->expandMacro('link', 'p', 'filter')) );*/
Assert::same( '<?php echo $control->link("p:a") ?>',  item1($parser->expandMacro('link', 'p:a', '')) );
Assert::same( '<?php echo $control->link($dest) ?>',  item1($parser->expandMacro('link', '$dest', '')) );
Assert::same( '<?php echo $control->link($p:$a) ?>',  item1($parser->expandMacro('link', '$p:$a', '')) );
Assert::same( '<?php echo $control->link("$p:$a") ?>',  item1($parser->expandMacro('link', '"$p:$a"', '')) );
Assert::same( '<?php echo $control->link("p:a") ?>',  item1($parser->expandMacro('link', '"p:a"', '')) );
Assert::same( '<?php echo $control->link(\'p:a\') ?>',  item1($parser->expandMacro('link', "'p:a'", '')) );

Assert::same( '<?php echo $control->link("p", array(\'param\')) ?>',  item1($parser->expandMacro('link', 'p param', '')) );
Assert::same( '<?php echo $control->link("p", array(\'param\' => 123)) ?>',  item1($parser->expandMacro('link', 'p param => 123', '')) );
Assert::same( '<?php echo $control->link("p", array(\'param\' => 123)) ?>',  item1($parser->expandMacro('link', 'p, param => 123', '')) );
