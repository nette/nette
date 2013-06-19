<?php

/**
 * Test: Nette\Forms\Controls\SelectBox.
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
	$input = $form->addSelect('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	));

	Assert::type('Nette\Utils\Html', $input->getLabel());
	Assert::same('<label for="frm-list">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-list">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<select name="list" id="frm-list"><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});



test(function() { // selected
	$form = new Form();
	$input = $form->addSelect('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	))->setValue(0);

	Assert::same('<select name="list" id="frm-list"><option value="a">First</option><option value="0" selected="selected">Second</option></select>', (string) $input->getControl());
});



test(function() { // translator & groups
	$form = new Form();
	$input = $form->addSelect('list', 'Label', array(
		'a' => 'First',
		'group' => array('Second', 'Third'),
	))->setPrompt('Prompt');
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-list">LABEL</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-list">ANOTHER LABEL</label>', (string) $input->getLabel('Another label'));
	Assert::same('<select name="list" id="frm-list"><option value="">PROMPT</option><option value="a">FIRST</option><optgroup label="GROUP"><option value="0">SECOND</option><option value="1">THIRD</option></optgroup></select>', (string) $input->getControl());
});



test(function() { // Html with translator & groups
	$form = new Form();
	$input = $form->addSelect('list', Html::el('b', 'Label'), array(
		'a' => Html::el('option', 'First')->class('class'),
		'group' => array(Html::el('option', 'Second')),
	))->setPrompt(Html::el('option', 'Prompt')->class('class'));
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-list"><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label for="frm-list">&lt;B&gt;ANOTHER LABEL&lt;/B&gt;</label>', (string) $input->getLabel(Html::el('b', 'Another label')));
	Assert::same('<select name="list" id="frm-list"><option class="class" value="">Prompt</option><option class="class">First</option><optgroup label="GROUP"><option>Second</option></optgroup></select>', (string) $input->getControl());
});



test(function() { // validation rules
	$form = new Form();
	$input = $form->addSelect('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	))->setRequired('required');

	Assert::same('<select name="list" id="frm-list" required="required" data-nette-rules=\'[{"op":":filled","msg":"required"}]\'><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});



test(function() { // container
	$form = new Form();
	$container = $form->addContainer('container');
	$input = $container->addSelect('list', 'Label', array(
		'a' => 'First',
		0 => 'Second',
	));

	Assert::same('<select name="container[list]" id="frm-container-list"><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});
