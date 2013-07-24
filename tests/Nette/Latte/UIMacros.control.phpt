<?php

/**
 * Test: Nette\Latte\Macros\UIMacros: {control ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte\Macros\UIMacros;


require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
UIMacros::install($compiler);

// {control ...}
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->render() ?>',  $compiler->expandMacro('control', 'form', '')->openingCode );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->render(); echo $template->filter(%a%) ?>',  $compiler->expandMacro('control', 'form', 'filter')->openingCode );
Assert::match( '<?php if (is_object($form)) %a% else %a% $_control->getComponent($form); %a%->render() ?>',  $compiler->expandMacro('control', '$form', '')->openingCode );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->renderType() ?>',  $compiler->expandMacro('control', 'form:type', '')->openingCode );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->{"render$type"}() ?>',  $compiler->expandMacro('control', 'form:$type', '')->openingCode );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->renderType(\'param\') ?>',  $compiler->expandMacro('control', 'form:type param', '')->openingCode );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->renderType(array(\'param\' => 123)) ?>',  $compiler->expandMacro('control', 'form:type param => 123', '')->openingCode );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->renderType(array(\'param\' => 123)) ?>',  $compiler->expandMacro('control', 'form:type, param => 123', '')->openingCode );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->render(); echo $template->striptags(%a%) ?>',  $compiler->expandMacro('control', 'form', 'striptags')->openingCode );
