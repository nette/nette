<?php

/**
 * Test: Nette\Latte\Macros\FormMacros: n:input
 *
 * @author     Filip Procházka
 * @package    Nette\Latte
 */

use Nette\Latte\Macros\FormMacros;



require __DIR__ . '/../bootstrap.php';


function assertTemplate($expected, $source)
{
	$template = new Nette\Templating\Template();
	$template->registerFilter(function ($s) {
		$parser = new Nette\Latte\Parser();
		$compiler = new Nette\Latte\Compiler;
		FormMacros::install($compiler);

		return $compiler->compile($parser->parse($s));
	});

	$form = new Nette\Forms\Form();
	$form->addText('name')->setValue("Lister");
	$form->addSelect('sex')->setItems(array(1 => 'Man', 2 => 'Woman'))->setValue(2);
	$form->addTextArea('bio')->setValue("I'm just writing this.");
	$template->form = $template->_form = $form;

	ob_start();
	try {
		$template->setSource($source);
		$template->render();
		$output = ob_get_clean();

	} catch (\Exception $e) {
		ob_end_clean();
		throw $e;
	}

	Assert::match($expected, $output);
}

assertTemplate('<input  type="text" name="name" id="frm-name" value="Lister"/>', '<input n:input="name">');
assertTemplate('<SeleCt name="sex" id="frm-sex"><option value="1">Man</option><option value="2" selected="selected">Woman</option></select>', '<SeleCt n:input="sex"></select>');
assertTemplate('<select  name="sex" id="frm-sex"><option value="1">Man</option><option value="2" selected="selected">Woman</option></select>', '<select n:input="sex"/>');
assertTemplate('<textarea cols="40" rows="10" name="bio" id="frm-bio">I\'m just writing this.</Textarea>', '<textarea n:input="bio"></Textarea>');
assertTemplate('<textarea  cols="40" rows="10" name="bio" id="frm-bio">I\'m just writing this.</textarea>', '<textarea n:input="bio" />');

