<?php

/**
 * Test: Nette\Forms\Controls\Button & SubmitButton
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
	$input = $form->addButton('button', 'Caption');

	Assert::null($input->getLabel());
	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<input type="button" name="button" value="Caption">', (string) $input->getControl());
	Assert::same('<input type="button" name="button" value="Another caption">', (string) $input->getControl('Another caption'));
});


test(function() { // translator
	$form = new Form;
	$input = $form->addButton('button', 'Caption');
	$input->setTranslator(new Translator);

	Assert::same('<input type="button" name="button" value="CAPTION">', (string) $input->getControl());
	Assert::same('<input type="button" name="button" value="ANOTHER CAPTION">', (string) $input->getControl('Another caption'));
});


test(function() { // Html with translator
	$form = new Form;
	$input = $form->addButton('button', Html::el('b', 'Caption'));
	$input->setTranslator(new Translator);

	Assert::same('<input type="button" name="button" value="<b>Caption</b>">', (string) $input->getControl());
	Assert::same('<input type="button" name="button" value="<b>Another label</b>">', (string) $input->getControl(Html::el('b', 'Another label')));
});


test(function() { // no validation rules
	$form = new Form;
	$input = $form->addButton('button', 'Caption')->setRequired('required');

	Assert::same('<input type="button" name="button" value="Caption">', (string) $input->getControl());
});


test(function() { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addButton('button', 'Caption');

	Assert::same('<input type="button" name="container[button]" value="Caption">', (string) $input->getControl());
});


test(function() { // SubmitButton
	$form = new Form;
	$input = $form->addSubmit('button', 'Caption');

	Assert::null($input->getLabel());
	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<input type="submit" name="button" value="Caption">', (string) $input->getControl());
	Assert::same('<input type="submit" name="button" value="Another caption">', (string) $input->getControl('Another caption'));
});


test(function() { // SubmitButton with scope
	$form = new Form;
	$input = $form->addSubmit('button', 'Caption')->setValidationScope(FALSE);

	Assert::same('<input type="submit" name="button" value="Caption" formnovalidate>', (string) $input->getControl());
});


test(function() { // SubmitButton with scope
	$form = new Form;
	$text = $form->addText('text');
	$select = $form->addSelect('select');
	$input = $form->addSubmit('button', 'Caption')->setValidationScope(array($text, $select));

	Assert::same('<input type="submit" name="button" value="Caption" formnovalidate data-nette-validation-scope=\'["text","select"]\'>', (string) $input->getControl());
});


test(function() { // forced ID
	$form = new Form;
	$input = $form->addButton('button', 'Caption');
	$input->setHtmlId( $input->getHtmlId() );

	Assert::same('<input type="button" name="button" id="frm-button" value="Caption">', (string) $input->getControl());
});
