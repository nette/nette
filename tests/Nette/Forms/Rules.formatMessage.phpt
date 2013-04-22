<?php

/**
 * Test: Nette\Forms\Rules::validateMessage()
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$form = new Form;
$form->addText('email', 'Email')
	->addRule(Form::EMAIL, '%label %value is invalid [field %name]')
	->setDefaultValue('xyz');

Assert::match('%A%data-nette-rules=\'[{"op":":email","msg":"Email %value is invalid [field email]"}]\'%A%', $form->__toString(TRUE));

$form->validate();

Assert::same( array(), $form->getErrors() );

Assert::same( array(
	"Email xyz is invalid [field email]",
), $form->getAllErrors() );

Assert::same( array(
	"Email xyz is invalid [field email]",
), $form['email']->getErrors() );
