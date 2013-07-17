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
$form->setMethod('get');
$form->setAction('?arg=val');
$form->addSubmit('send', 'Sign in');

$template = new FileTemplate(__DIR__ . '/templates/forms.get.latte');
$template->registerFilter(new Latte\Engine);
$template->_control = array('myForm' => $form);


$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", codefix($template->compile()));
Assert::matchFile("$path.html", $template->__toString(TRUE));
