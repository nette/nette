<?php

/**
 * Test: Nette\Latte\Macros\UIMacros: {link ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\Macros\UIMacros;


require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
$compiler->setContentType(Nette\Latte\Compiler::CONTENT_TEXT);
UIMacros::install($compiler);

// {link ...}
Assert::same( '<?php echo $_control->link("p") ?>',  $compiler->expandMacro('link', 'p', '')->openingCode );
Assert::same( '<?php echo $template->filter($_control->link("p")) ?>',  $compiler->expandMacro('link', 'p', 'filter')->openingCode );
Assert::same( '<?php echo $_control->link("p:a") ?>',  $compiler->expandMacro('link', 'p:a', '')->openingCode );
Assert::same( '<?php echo $_control->link($dest) ?>',  $compiler->expandMacro('link', '$dest', '')->openingCode );
Assert::same( '<?php echo $_control->link($p:$a) ?>',  $compiler->expandMacro('link', '$p:$a', '')->openingCode );
Assert::same( '<?php echo $_control->link("$p:$a") ?>',  $compiler->expandMacro('link', '"$p:$a"', '')->openingCode );
Assert::same( '<?php echo $_control->link("p:a") ?>',  $compiler->expandMacro('link', '"p:a"', '')->openingCode );
Assert::same( '<?php echo $_control->link(\'p:a\') ?>',  $compiler->expandMacro('link', "'p:a'", '')->openingCode );

Assert::same( '<?php echo $_control->link("p", array(\'param\')) ?>',  $compiler->expandMacro('link', 'p param', '')->openingCode );
Assert::same( '<?php echo $_control->link("p", array(\'param\' => 123)) ?>',  $compiler->expandMacro('link', 'p param => 123', '')->openingCode );
Assert::same( '<?php echo $_control->link("p", array(\'param\' => 123)) ?>',  $compiler->expandMacro('link', 'p, param => 123', '')->openingCode );
