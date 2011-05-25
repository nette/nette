<?php

/**
 * Test: Nette\Latte\DefaultMacros::macroControl()
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

// {control ...}
Assert::match( '<?php %a% $control->getWidget("form"); %a%->render() ?>',  item1($parser->expandMacro('control', 'form', '')) );
Assert::match( '<?php %a% $control->getWidget("form"); %a%->render() ?>',  item1($parser->expandMacro('control', 'form', 'filter')) );
Assert::match( '<?php if (is_object($form)) %a% else %a% $control->getWidget($form); %a%->render() ?>',  item1($parser->expandMacro('control', '$form', '')) );
Assert::match( '<?php %a% $control->getWidget("form"); %a%->renderType() ?>',  item1($parser->expandMacro('control', 'form:type', '')) );
Assert::match( '<?php %a% $control->getWidget("form"); %a%->{"render$type"}() ?>',  item1($parser->expandMacro('control', 'form:$type', '')) );
Assert::match( '<?php %a% $control->getWidget("form"); %a%->renderType(\'param\') ?>',  item1($parser->expandMacro('control', 'form:type param', '')) );
Assert::match( '<?php %a% $control->getWidget("form"); %a%->renderType(array(\'param\' => 123)) ?>',  item1($parser->expandMacro('control', 'form:type param => 123', '')) );
Assert::match( '<?php %a% $control->getWidget("form"); %a%->renderType(array(\'param\' => 123)) ?>',  item1($parser->expandMacro('control', 'form:type, param => 123', '')) );
