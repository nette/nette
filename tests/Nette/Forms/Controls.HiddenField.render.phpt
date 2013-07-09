<?php

/**
 * Test: Nette\Forms\Controls\HiddenField.
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
	$input = $form->addHidden('hidden', 'value');

	Assert::null($input->getLabel());
	Assert::type('Nette\Utils\Html', $input->getControl());
	Assert::same('<input type="hidden" name="hidden" value="value">', (string) $input->getControl());
});


test(function() { // no validation rules
	$form = new Form;
	$input = $form->addHidden('hidden')->setRequired('required');

	Assert::same('<input type="hidden" name="hidden" value="">', (string) $input->getControl());
});


test(function() { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addHidden('hidden');

	Assert::same('<input type="hidden" name="container[hidden]" value="">', (string) $input->getControl());
});


test(function() { // forced ID
	$form = new Form;
	$input = $form->addHidden('hidden')->setRequired('required');
	$input->setHtmlId( $input->getHtmlId() );

	Assert::same('<input type="hidden" name="hidden" id="frm-hidden" value="">', (string) $input->getControl());
});
