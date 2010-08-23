<?php

/**
 * Test: Nette\Forms\Rules::validateMessage()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\Forms\Form;



require __DIR__ . '/../initialize.php';



$form = new Form;
$form->addText('email', 'E-mail')
	->addRule(Form::EMAIL, '%label %value is invalid [field %name]')
	->setDefaultValue('xyz');

Assert::match("%A%data-nette-rules=\"{op:':email',msg:'E-mail %value is invalid [field email]'}\"%A%", (string) $form);

$form->validate();

Assert::same( array(
	"E-mail xyz is invalid [field email]",
), $form->getErrors() );
