<?php

/**
 * Test: Nette\Forms\Controls\TextInput.
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
	$input = $form->addText('text', 'Label')
		->setValue('text')
		->setAttribute('autocomplete', 'off');

	Assert::type('Nette\Utils\Html', $input->getLabel());
	Assert::same('<label for="frm-text">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<input type="text" name="text" autocomplete="off" id="frm-text" value="text">', (string) $input->getControl());
});


test(function() { // translator
	$form = new Form;
	$input = $form->addText('text', 'Label')
		->setAttribute('placeholder', 'place')
		->setValue('text')
		->setTranslator(new Translator)
		->setEmptyValue('xxx');

	Assert::same('<label for="frm-text">LABEL</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text">ANOTHER LABEL</label>', (string) $input->getLabel('Another label'));
	Assert::same('<input type="text" name="text" placeholder="PLACE" id="frm-text" data-nette-empty-value="XXX" value="text">', (string) $input->getControl());
});


test(function() { // Html with translator
	$form = new Form;
	$input = $form->addText('text', Html::el('b', 'Label'))
		->setTranslator(new Translator);

	Assert::same('<label for="frm-text"><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
});


test(function() { // password
	$form = new Form;
	$input = $form->addPassword('password')
		->setValue('xxx');

	Assert::same('<input type="password" name="password" id="frm-password">', (string) $input->getControl());
});


test(function() { // validation rule required & PATTERN
	$form = new Form;
	$input = $form->addText('text')
		->setRequired('required')
		->addRule($form::PATTERN, 'error message', '[0-9]+');

	Assert::same('<input type="text" name="text" id="frm-text" required data-nette-rules=\'[{"op":":filled","msg":"required"},{"op":":pattern","msg":"error message","arg":"[0-9]+"}]\' pattern="[0-9]+" value="">', (string) $input->getControl());
});


test(function() { // conditional required
	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::FILLED)
			->addRule($form::FILLED);

	Assert::same('<input type="text" name="text" id="frm-text" data-nette-rules=\'[{"op":":filled","rules":[{"op":":filled","msg":"Please complete mandatory field."}],"control":"text"}]\' value="">', (string) $input->getControl());
});


test(function() { // validation rule LENGTH
	$form = new Form;
	$input = $form->addText('text')
		->addRule($form::LENGTH, NULL, array(10, 20));

	Assert::same('<input type="text" name="text" maxlength="20" id="frm-text" data-nette-rules=\'[{"op":":length","msg":"Please enter a value between 10 and 20 characters long.","arg":[10,20]}]\' value="">', (string) $input->getControl());
});


test(function() { // validation rule MAX_LENGTH
	$form = new Form;
	$input = $form->addText('text')
		->addRule($form::MAX_LENGTH, NULL, 10);

	Assert::same('<input type="text" name="text" maxlength="10" id="frm-text" data-nette-rules=\'[{"op":":maxLength","msg":"Please enter a value no longer than 10 characters.","arg":10}]\' value="">', (string) $input->getControl());
});


test(function() { // validation rule RANGE & setType
	$form = new Form;
	$input = $form->addText('count')
		->setType('number')
		->addRule(Form::RANGE, 'Must be in range from %d to %d', array(1, 100));

	Assert::same('<input type="number" name="count" id="frm-count" data-nette-rules=\'[{"op":":range","msg":"Must be in range from 1 to 100","arg":[1,100]}]\' min="1" max="100" value="">', (string) $input->getControl());
});


test(function() { // setEmptyValue
	$form = new Form;
	$input = $form->addText('text')
		->setEmptyValue('empty');

	Assert::same('<input type="text" name="text" id="frm-text" data-nette-empty-value="empty" value="empty">', (string) $input->getControl());
});


test(function() { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addText('text');

	Assert::same('<input type="text" name="container[text]" id="frm-container-text" value="">', (string) $input->getControl());
});
