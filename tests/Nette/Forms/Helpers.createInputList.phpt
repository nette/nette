<?php

/**
 * Test: Nette\Forms\Helpers::createInputList()
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Helpers;


require __DIR__ . '/../bootstrap.php';


class Translator implements Nette\Localization\ITranslator
{
	function translate($s, $plural = NULL)
	{
		return strtoupper($s);
	}
}


test(function() {
	Assert::same('', Helpers::createInputList(array()));

	Assert::same('<label><input value="0">a</label>', Helpers::createInputList(array('a')));

	Assert::same('<label><input value="a">First</label><label><input value="b">Second</label>', Helpers::createInputList(
		array('a' => 'First', 'b' => 'Second')
	));

	Assert::same('<label class="button"><input type="checkbox" value="a">First</label><label class="button"><input type="checkbox" value="b">Second</label>', Helpers::createInputList(
		array('a' => 'First', 'b' => 'Second'),
		array('type' => 'checkbox'),
		array('class' => 'button')
	));

	Assert::same('<label style="color:blue" class="class1 class2"><input title="Hello" type="checkbox" checked value="a">First</label><label style="color:blue"><input title="Hello" type="radio" value="b">Second</label>', Helpers::createInputList(
		array('a' => 'First', 'b' => 'Second'),
		array(
			'type|*' => array('a' => 'checkbox', 'b' => 'radio'),
			'checked|?' => array('a'),
			'title' => 'Hello',
		),
		array(
			'class|*' => array('a' => array('class1', 'class2')),
			'style' => array('color' => 'blue'),
		)
	));

	Assert::same('<label><input value="a">FIRST</label><label><input value="b">SECOND</label>', Helpers::createInputList(
		array('a' => 'First', 'b' => 'Second'),
		NULL,
		NULL,
		new Translator
	));

	Assert::same('<label><input value="a">First</label><br><label><input value="b">Second</label><br>', Helpers::createInputList(
		array('a' => 'First', 'b' => 'Second'),
		NULL,
		NULL,
		NULL,
		'<br>'
	));
});
