<?php

/**
 * Test: Nette\Forms\Controls\TextArea.
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
	$input = $form->addTextArea('text', 'Label')
		->setValue('&text')
		->setAttribute('autocomplete', 'off');

	Assert::type('Nette\Utils\Html', $input->getLabel());
	Assert::same('<label for="frm-text">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<textarea name="text" autocomplete="off" id="frm-text">&amp;text</textarea>', (string) $input->getControl());
});


test(function() { // translator
	$form = new Form;
	$input = $form->addTextArea('text', 'Label')
		->setAttribute('placeholder', 'place')
		->setValue('text')
		->setTranslator(new Translator);

	Assert::same('<label for="frm-text">LABEL</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text">ANOTHER LABEL</label>', (string) $input->getLabel('Another label'));
	Assert::same('<textarea name="text" placeholder="PLACE" id="frm-text">text</textarea>', (string) $input->getControl());
});


test(function() { // Html with translator
	$form = new Form;
	$input = $form->addTextArea('text', Html::el('b', 'Label'))
		->setTranslator(new Translator);

	Assert::same('<label for="frm-text"><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
});


test(function() { // validation rule LENGTH
	$form = new Form;
	$input = $form->addTextArea('text')
		->addRule($form::LENGTH, NULL, array(10, 20));

	Assert::same('<textarea name="text" maxlength="20" id="frm-text" data-nette-rules=\'[{"op":":length","msg":"Please enter a value between 10 and 20 characters long.","arg":[10,20]}]\'></textarea>', (string) $input->getControl());
});


test(function() { // validation rule MAX_LENGTH
	$form = new Form;
	$input = $form->addTextArea('text')
		->addRule($form::MAX_LENGTH, NULL, 10);

	Assert::same('<textarea name="text" maxlength="10" id="frm-text" data-nette-rules=\'[{"op":":maxLength","msg":"Please enter a value no longer than 10 characters.","arg":10}]\'></textarea>', (string) $input->getControl());
});


test(function() { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addTextArea('text');

	Assert::same('<textarea name="container[text]" id="frm-container-text"></textarea>', (string) $input->getControl());
});
