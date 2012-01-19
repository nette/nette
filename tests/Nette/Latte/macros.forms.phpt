<?php

/**
 * Test: Nette\Latte\Engine and FormMacros.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



TestHelpers::purge(TEMP_DIR);


$form = new Form;
$form->addText('username', 'Username:');
$form->addPassword('password', 'Password:');
$form->addSubmit('send', 'Sign in');


$form2 = new Form;
$form2->addText('text1', 'Text 1');
$cont1 = $form2->addContainer('cont1');
$cont1->addText('text2', 'Text 2');
$cont2 = $cont1->addContainer('cont2');
$cont2->addText('text3', 'Text 3');
$cont2->addText('text4', 'Text 4');
$cont1->addText('text5', 'Text 5');
$form2->addText('text6', 'Text 6');


$template = new FileTemplate(__DIR__ . '/templates/forms.latte');
$template->registerFilter(new Latte\Engine);
$template->_control = array('myForm' => $form, 'myForm2' => $form2);

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), codefix($template->compile()));
Assert::match(file_get_contents("$path.html"), $template->__toString(TRUE));
