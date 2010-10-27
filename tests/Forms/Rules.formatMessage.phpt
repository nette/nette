<?php

/**
 * Test: Nette\Forms\Rules::validateMessage()
 *
 * @author     David Grudl
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$form = new Form;
$form->addText('email', 'E-mail')
	->addRule(Form::EMAIL, '%label %value is invalid [field %name]')
	->setDefaultValue('xyz');

Assert::match("%A%data-nette-rules=\"{op:':email',msg:'E-mail %value is invalid [field email]'}\"%A%", $form->__toString(TRUE));

$form->validate();

Assert::same( array(
	"E-mail xyz is invalid [field email]",
), $form->getErrors() );
