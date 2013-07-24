<?php

/**
 * Test: Nette\Latte\Engine and FormMacros.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Forms\Form;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$form = new Form;
$form->addHidden('id');
$form->addText('username', 'Username:'); // must have just one textfield to generate IE fix
$form->addRadioList('sex', 'Sex:', array('m' => 'male', 'f' => 'female'));
$form->addSelect('select', NULL, array('m' => 'male', 'f' => 'female'));
$form->addTextArea('area', NULL)->setValue('one<two');
$form->addSubmit('send', 'Sign in');

$template = new FileTemplate(__DIR__ . '/templates/forms.latte');
$template->registerFilter(new Latte\Engine);
$template->_control = array('myForm' => $form);


$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
//echo $template->compile(); exit;
Assert::matchFile("$path.phtml", codefix($template->compile()));
Assert::matchFile("$path.html", $template->__toString(TRUE));
