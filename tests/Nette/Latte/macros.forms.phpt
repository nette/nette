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

$template = new FileTemplate(__DIR__ . '/templates/forms.latte');
$template->registerFilter(new Latte\Engine);
$template->_control = array('myForm' => $form);


$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), codefix($template->compile()));
Assert::match(file_get_contents("$path.html"), $template->__toString(TRUE));
