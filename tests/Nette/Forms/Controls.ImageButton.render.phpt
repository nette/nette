<?php

/**
 * Test: Nette\Forms\Controls\Button.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form,
	Nette\Utils\Html;



require __DIR__ . '/../bootstrap.php';


class Translator implements Nette\Localization\ITranslator
{
	function translate($s, $plural = NULL)
	{
		return strtoupper($s);
	}
}



test(function() {
	$form = new Form;
	$input = $form->addImage('button', 'image.gif');

	Assert::null($input->getLabel());
	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<input type="image" src="image.gif" name="button[]" id="frm-button" />', (string) $input->getControl());
});



test(function() { // translator
	$form = new Form;
	$input = $form->addImage('button', 'image.gif');
	$input->setTranslator(new Translator);

	Assert::same('<input type="image" src="image.gif" name="button[]" id="frm-button" />', (string) $input->getControl());
});



test(function() { // validation rules
	$form = new Form;
	$input = $form->addImage('button', 'image.gif')->setRequired('required');

	Assert::same('<input type="image" src="image.gif" name="button[]" id="frm-button" required="required" data-nette-rules=\'[{"op":":filled","msg":"required"}]\' />', (string) $input->getControl());
});



test(function() { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addImage('button', 'image.gif');

	Assert::same('<input type="image" src="image.gif" name="container[button][]" id="frm-container-button" />', (string) $input->getControl());
});
