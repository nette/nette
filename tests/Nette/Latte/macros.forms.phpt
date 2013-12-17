<?php

/**
 * Test: Nette\Latte\Engine and FormMacros.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$form = new Form;
$form->addText('username', 'Username:');
$form->addPassword('password', 'Password:');
$form->addSubmit('send', 'Sign in');

$template = new FileTemplate(__DIR__ . '/templates/forms.latte');
$template->registerFilter(new Latte\Engine);
$template->_control = array('myForm' => $form);


$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", codefix($template->compile()));
Assert::matchFile("$path.html", $template->__toString(TRUE));
