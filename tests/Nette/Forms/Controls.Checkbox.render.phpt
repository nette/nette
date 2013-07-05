<?php

/**
 * Test: Nette\Forms\Controls\Checkbox.
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
	$input = $form->addCheckbox('on', 'Label');

	Assert::type('Nette\Utils\Html', $input->getLabel());
	Assert::same('<label for="frm-on">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-on">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<input type="checkbox" name="on" id="frm-on">', (string) $input->getControl());

	$input->setValue(TRUE);
	Assert::same('<input type="checkbox" name="on" id="frm-on" checked>', (string) $input->getControl());
});


test(function() { // Html with translator
	$form = new Form;
	$input = $form->addCheckbox('on', 'Label');
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-on">LABEL</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-on">ANOTHER LABEL</label>', (string) $input->getLabel('Another label'));
	Assert::same('<label for="frm-on"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
});


test(function() { // validation rules
	$form = new Form;
	$input = $form->addCheckbox('on')->setRequired('required');

	Assert::same('<input type="checkbox" name="on" id="frm-on" required data-nette-rules=\'[{"op":":filled","msg":"required"}]\'>', (string) $input->getControl());
});


test(function() { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addCheckbox('on');

	Assert::same('<input type="checkbox" name="container[on]" id="frm-container-on">', (string) $input->getControl());
});
