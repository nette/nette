<?php

/**
 * Test: Nette\Forms\Controls\RadioList.
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
	$form = new Form();
	$input = $form->addRadioList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	));

	Assert::type('Nette\Utils\Html', $input->getLabel());
	Assert::same('<label>Label</label>', (string) $input->getLabel());
	Assert::same('<label>Another label</label>', (string) $input->getLabel('Another label'));
	Assert::same('<label for="frm-list-0">Second</label>', (string) $input->getLabel(NULL, 0));
	Assert::same('<label for="frm-list-0">Another label</label>', (string) $input->getLabel('Another label', 0));

	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<input type="radio" name="list" id="frm-list-a" value="a" /><label for="frm-list-a">First</label><br /><input type="radio" name="list" id="frm-list-0" value="0" /><label for="frm-list-0">Second</label><br />', (string) $input->getControl());
	Assert::same('<input type="radio" name="list" id="frm-list-0" value="0" />', (string) $input->getControl(0));
});



test(function() { // checked
	$form = new Form();
	$input = $form->addRadioList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	))->setValue(0);

	Assert::same('<input type="radio" name="list" id="frm-list-a" value="a" /><label for="frm-list-a">First</label><br /><input type="radio" name="list" id="frm-list-0" checked="checked" value="0" /><label for="frm-list-0">Second</label><br />', (string) $input->getControl());
});



test(function() { // translator
	$form = new Form();
	$input = $form->addRadioList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	));
	$input->setTranslator(new Translator);

	Assert::same('<label>LABEL</label>', (string) $input->getLabel());
	Assert::same('<label>ANOTHER LABEL</label>', (string) $input->getLabel('Another label'));
	Assert::same('<label for="frm-list-0">SECOND</label>', (string) $input->getLabel(NULL, 0));
	Assert::same('<label for="frm-list-0">ANOTHER LABEL</label>', (string) $input->getLabel('Another label', 0));

	Assert::same('<input type="radio" name="list" id="frm-list-a" value="a" /><label for="frm-list-a">FIRST</label><br /><input type="radio" name="list" id="frm-list-0" value="0" /><label for="frm-list-0">SECOND</label><br />', (string) $input->getControl());
	Assert::same('<input type="radio" name="list" id="frm-list-0" value="0" />', (string) $input->getControl(0));
});



test(function() { // Html
	$form = new Form();
	$input = $form->addRadioList('list', Html::el('b', 'Label'), array(
		'a' => Html::el('b', 'First'),
	));
	$input->setTranslator(new Translator);

	Assert::same('<label><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label>&lt;B&gt;ANOTHER LABEL&lt;/B&gt;</label>', (string) $input->getLabel(Html::el('b', 'Another label')));
	Assert::same('<label for="frm-list-0">&lt;B&gt;ANOTHER LABEL&lt;/B&gt;</label>', (string) $input->getLabel(Html::el('b', 'Another label'), 0));

	Assert::same('<input type="radio" name="list" id="frm-list-a" value="a" /><label for="frm-list-a"><b>First</b></label><br />', (string) $input->getControl());
	Assert::same('<input type="radio" name="list" id="frm-list-0" value="0" />', (string) $input->getControl(0));
});



test(function() { // validation rules
	$form = new Form();
	$input = $form->addRadioList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	))->setRequired('required');

	Assert::same('<input type="radio" name="list" id="frm-list-a" required="required" data-nette-rules=\'[{"op":":filled","msg":"required"}]\' value="a" /><label for="frm-list-a">First</label><br /><input type="radio" name="list" id="frm-list-0" required="required" value="0" /><label for="frm-list-0">Second</label><br />', (string) $input->getControl());
	Assert::same('<input type="radio" name="list" id="frm-list-0" required="required" data-nette-rules=\'[{"op":":filled","msg":"required"}]\' value="0" />', (string) $input->getControl(0));
});



test(function() { // container
	$form = new Form();
	$container = $form->addContainer('container');
	$input = $container->addRadioList('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	));

	Assert::same('<input type="radio" name="container[list]" id="frm-container-list-a" value="a" /><label for="frm-container-list-a">First</label><br /><input type="radio" name="container[list]" id="frm-container-list-0" value="0" /><label for="frm-container-list-0">Second</label><br />', (string) $input->getControl());
});
