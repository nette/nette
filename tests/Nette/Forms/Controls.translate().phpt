<?php

/**
 * Test: Nette\Forms translating controls with translatable strings wrapped in objects
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Translator implements \Nette\Localization\ITranslator
{
	function translate($message, $count = NULL)
	{
		return is_object($message) ? get_class($message) : $message;
	}
}

class StringWrapper
{
	public $message;

	public function __construct($message)
	{
		$this->message = $message;
	}

	public function __toString()
	{
		return (string) $this->message;
	}
}



test(function() {
	$form = new Form;
	$form->setTranslator(new Translator);

	$name = $form->addText('name', 'Your name');
	Assert::match('<label for="frm-name">Your name</label>', (string) $name->getLabel());

	$name2 = $form->addText('name2', new StringWrapper('Your name'));
	Assert::match('<label for="frm-name2">StringWrapper</label>', (string) $name2->getLabel());
});



test(function() {
	$form = new Form;
	$form->setTranslator(new Translator);

	$name = $form->addRadioList('name', 'Your name');
	Assert::match('<label>Your name</label>', (string) $name->getLabel());

	$name2 = $form->addRadioList('name2', new StringWrapper('Your name'));
	Assert::match('<label>StringWrapper</label>', (string) $name2->getLabel());
});



test(function() {
	$form = new Form;
	$form->setTranslator(new Translator);

	$name = $form->addText('name', 'Your name');
	$name->addError("Error message");
	$name->addError($w = new StringWrapper('Your name'));
	Assert::same(array(
		'Error message',
		$w
	), $name->getErrors());
});



test(function() {
	$form = new Form;
	$form->setTranslator(new Translator);

	$email = $form->addText('email')
		->addRule($form::EMAIL, 'error');
	Assert::match('<input type="text" name="email" id="frm-email" data-nette-rules=\'[{"op":":email","msg":"error"}]\' value="">', (string) $email->getControl());
	$email->validate();
	Assert::same(array('error'), $email->getErrors());

	$email2 = $form->addText('email2')
		->addRule($form::EMAIL, new StringWrapper('Your name'));
	Assert::match('<input type="text" name="email2" id="frm-email2" data-nette-rules=\'[{"op":":email","msg":"StringWrapper"}]\' value="">', (string) $email2->getControl());
	$email2->validate();
	Assert::same(array('StringWrapper'), $email2->getErrors());
});
