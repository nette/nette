<?php

/**
 * Test: Nette\Templates\LatteMacros::macroControl()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';


$macros = new LatteMacros;

// {control ...}
Assert::same( '$control->getWidget("form")->render()',  $macros->macroControl('form', '') );
Assert::same( '$control->getWidget("form")->render()',  $macros->macroControl('form', 'filter') );
Assert::same( 'if (is_object($form)) $form->render(); else $control->getWidget($form)->render()',  $macros->macroControl('$form', '') );
Assert::same( '$control->getWidget("form")->renderType()',  $macros->macroControl('form:type', '') );
Assert::same( '$control->getWidget("form")->{"render$type"}()',  $macros->macroControl('form:$type', '') );
Assert::same( '$control->getWidget("form")->renderType(\'param\')',  $macros->macroControl('form:type param', '') );
Assert::same( '$control->getWidget("form")->renderType(array(\'param\' => 123))',  $macros->macroControl('form:type param => 123', '') );
Assert::same( '$control->getWidget("form")->renderType(array(\'param\' => 123))',  $macros->macroControl('form:type, param => 123', '') );
