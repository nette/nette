<?php

/**
 * Test: Nette\Forms\Controls\Checkbox.
 */

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Translator implements Nette\Localization\ITranslator
{
	function translate($s, $plural = NULL)
	{
		return strtoupper($s);
	}
}


test(function () {
	$form = new Form;
	$input = $form->addCheckbox('on', 'Label');

	Assert::null($input->getLabel());

	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<label for="frm-on"><input type="checkbox" name="on" id="frm-on">Label</label>', (string) $input->getControl());

	Assert::type('Nette\Utils\Html', $input->getLabelPart());
	Assert::same('<label for="frm-on">Label</label>', (string) $input->getLabelPart());

	Assert::type('Nette\Utils\Html', $input->getControlPart());
	Assert::same('<input type="checkbox" name="on" id="frm-on">', (string) $input->getControlPart());

	$input->setValue(TRUE);
	Assert::same('<label for="frm-on"><input type="checkbox" name="on" id="frm-on" checked>Label</label>', (string) $input->getControl());
	Assert::same('<input type="checkbox" name="on" id="frm-on" checked>', (string) $input->getControlPart());
});


test(function () { // Html with translator
	$form = new Form;
	$input = $form->addCheckbox('on', 'Label');
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-on"><input type="checkbox" name="on" id="frm-on">LABEL</label>', (string) $input->getControl());
});


test(function () { // validation rules
	$form = new Form;
	$input = $form->addCheckbox('on')->setRequired('required');

	Assert::same('<label for="frm-on"><input type="checkbox" name="on" id="frm-on" required data-nette-rules=\'[{"op":":filled","msg":"required"}]\'></label>', (string) $input->getControl());
});


test(function () { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addCheckbox('on');

	Assert::same('<label for="frm-container-on"><input type="checkbox" name="container[on]" id="frm-container-on"></label>', (string) $input->getControl());
});
