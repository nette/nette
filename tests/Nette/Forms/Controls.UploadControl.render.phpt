<?php

/**
 * Test: Nette\Forms\Controls\UploadControl.
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
	$input = $form->addUpload('file', 'Label');

	Assert::type('Nette\Utils\Html', $input->getLabel());
	Assert::same('<label for="frm-file">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-file">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<input type="file" name="file" id="frm-file">', (string) $input->getControl());
});


test(function() { // multiple
	$form = new Form;
	$input = $form->addUpload('file', 'Label', TRUE);

	Assert::same('<input type="file" name="file[]" multiple id="frm-file">', (string) $input->getControl());
});


test(function() { // Html with translator
	$form = new Form;
	$input = $form->addUpload('file', 'Label');
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-file">LABEL</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-file">ANOTHER LABEL</label>', (string) $input->getLabel('Another label'));
	Assert::same('<label for="frm-file"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
});


test(function() { // validation rules
	$form = new Form;
	$input = $form->addUpload('file')->setRequired('required');

	Assert::same('<input type="file" name="file" id="frm-file" required data-nette-rules=\'[{"op":":filled","msg":"required"}]\'>', (string) $input->getControl());
});


test(function() { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addUpload('file');

	Assert::same('<input type="file" name="container[file]" id="frm-container-file">', (string) $input->getControl());
});
