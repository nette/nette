<?php

/**
 * Test: Nette\Forms\Controls\CheckboxList.
 *
 * @author     David Grudl
 */

use Nette\Forms\Form,
	Nette\Utils\Html,
	Tester\Assert;


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
	$input = $form->addCheckboxList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	));

	Assert::type('Nette\Utils\Html', $input->getLabel());
	Assert::same('<label>Label</label>', (string) $input->getLabel());
	Assert::same('<label>Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type('Nette\Utils\Html', $input->getLabelPart(0));
	Assert::same('<label for="frm-list-0">Second</label>', (string) $input->getLabelPart(0));

	Assert::type('string', $input->getControl());
	Assert::same('<label><input type="checkbox" name="list[]" value="a">First</label><br><label><input type="checkbox" name="list[]" value="0">Second</label>', $input->getControl());

	Assert::type('Nette\Utils\Html', $input->getControlPart(0));
	Assert::same('<input type="checkbox" name="list[]" id="frm-list-0" value="0">', (string) $input->getControlPart(0));
});


test(function() { // checked
	$form = new Form;
	$input = $form->addCheckboxList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	))->setValue(0);

	Assert::same('<label><input type="checkbox" name="list[]" value="a">First</label><br><label><input type="checkbox" name="list[]" checked value="0">Second</label>', $input->getControl());
});


test(function() { // translator
	$form = new Form;
	$input = $form->addCheckboxList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	));
	$input->setTranslator(new Translator);

	Assert::same('<label>LABEL</label>', (string) $input->getLabel());
	Assert::same('<label>ANOTHER LABEL</label>', (string) $input->getLabel('Another label'));
	Assert::same('<label for="frm-list-0">SECOND</label>', (string) $input->getLabelPart(0));

	Assert::same('<label><input type="checkbox" name="list[]" value="a">FIRST</label><br><label><input type="checkbox" name="list[]" value="0">SECOND</label>', $input->getControl());
	Assert::same('<input type="checkbox" name="list[]" id="frm-list-0" value="0">', (string) $input->getControlPart(0));
});


test(function() { // Html
	$form = new Form;
	$input = $form->addCheckboxList('list', Html::el('b', 'Label'), array(
		'a' => Html::el('b', 'First'),
	));
	$input->setTranslator(new Translator);

	Assert::same('<label><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));

	Assert::same('<label><input type="checkbox" name="list[]" value="a"><b>First</b></label>', $input->getControl());
	Assert::same('<input type="checkbox" name="list[]" id="frm-list-a" value="a">', (string) $input->getControlPart('a'));
});


test(function() { // validation rules
	$form = new Form;
	$input = $form->addCheckboxList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	))->setRequired('required');

	Assert::same('<label><input type="checkbox" name="list[]" data-nette-rules=\'[{"op":":filled","msg":"required"}]\' value="a">First</label><br><label><input type="checkbox" name="list[]" value="0">Second</label>', $input->getControl());
	Assert::same('<input type="checkbox" name="list[]" id="frm-list-0" data-nette-rules=\'[{"op":":filled","msg":"required"}]\' value="0">', (string) $input->getControlPart(0));
});


test(function() { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addCheckboxList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	));

	Assert::same('<label><input type="checkbox" name="container[list][]" value="a">First</label><br><label><input type="checkbox" name="container[list][]" value="0">Second</label>', $input->getControl());
});


test(function() { // separator prototype
	$form = new Form;
	$input = $form->addCheckboxList('list', NULL, array(
		'a' => 'b',
	));
	$input->getSeparatorPrototype()->setName('div');

	Assert::same('<div><label><input type="checkbox" name="list[]" value="a">b</label></div>', $input->getControl());
});


test(function() { // disabled all
	$form = new Form;
	$input = $form->addCheckboxList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	))->setDisabled(TRUE);

	Assert::same('<label><input type="checkbox" name="list[]" disabled value="a">First</label><br><label><input type="checkbox" name="list[]" disabled value="0">Second</label>', $input->getControl());
});


test(function() { // disabled one
	$form = new Form;
	$input = $form->addCheckboxList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	))->setDisabled(array('a'));

	Assert::same('<label><input type="checkbox" name="list[]" disabled value="a">First</label><br><label><input type="checkbox" name="list[]" value="0">Second</label>', $input->getControl());
	Assert::same('<input type="checkbox" name="list[]" id="frm-list-a" disabled value="a">', (string) $input->getControlPart('a'));
});
