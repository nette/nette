<?php

/**
 * Test: Nette\Latte\Macros\UIMacros: {control ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\Macros\UIMacros;



require __DIR__ . '/../bootstrap.php';


$compiler = new Nette\Latte\Compiler;
UIMacros::install($compiler);
function item1($a) { return $a[1]; }

// {control ...}
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->render() ?>',  item1($compiler->expandMacro('control', 'form', '')) );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->render() ?>',  item1($compiler->expandMacro('control', 'form', 'filter')) );
Assert::match( '<?php if (is_object($form)) %a% else %a% $_control->getComponent($form); %a%->render() ?>',  item1($compiler->expandMacro('control', '$form', '')) );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->renderType() ?>',  item1($compiler->expandMacro('control', 'form:type', '')) );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->{"render$type"}() ?>',  item1($compiler->expandMacro('control', 'form:$type', '')) );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->renderType(\'param\') ?>',  item1($compiler->expandMacro('control', 'form:type param', '')) );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->renderType(array(\'param\' => 123)) ?>',  item1($compiler->expandMacro('control', 'form:type param => 123', '')) );
Assert::match( '<?php %a% $_control->getComponent("form"); %a%->renderType(array(\'param\' => 123)) ?>',  item1($compiler->expandMacro('control', 'form:type, param => 123', '')) );
