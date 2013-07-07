<?php

/**
 * Test: Nette\Forms\Helpers::createSelectBox()
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Helpers,
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
	Assert::type('Nette\Utils\Html', Helpers::createSelectBox(array()));

	Assert::same('<select></select>', (string) Helpers::createSelectBox(array()));

	Assert::same('<select><option value="0">a</option></select>', (string) Helpers::createSelectBox(array('a')));

	Assert::same('<select><option value="a">First</option><option value="b">Second</option></select>', (string) Helpers::createSelectBox(
		array('a' => 'First', 'b' => 'Second')
	));

	Assert::same('<select><option value="a">First</option><optgroup label="Group"><option value="0">A</option><option value="1">B</option></optgroup></select>', (string) Helpers::createSelectBox(
		array(
			'a' => 'First',
			'Group' => array('A', 'B'),
		)
	));

	Assert::same('<select><option id="item-a" value="a">Hello</option><optgroup label="Group"><option id="item-b" value="0">World</option></optgroup></select>', (string) Helpers::createSelectBox(
		array(
			'a' => Html::el('', 'Hello')->id('item-a'),
			'Group' => array(Html::el('', 'World')->id('item-b')),
		)
	));

	Assert::same('<select><option title="Hello" style="color:blue" value="a" selected>First</option><option title="Hello" style="color:blue" value="b" disabled>Second</option></select>', (string) Helpers::createSelectBox(
		array('a' => 'First', 'b' => 'Second'),
		array(
			'disabled|*' => array('b' => TRUE),
			'selected|?' => array('a'),
			'title' => 'Hello',
			'style' => array('color' => 'blue'),
		)
	));

	Assert::same('<select><option disabled value="a">First</option><option disabled value="b" selected>Second</option></select>', (string) Helpers::createSelectBox(
		array('a' => 'First', 'b' => 'Second'),
		array('disabled|*' => TRUE, 'selected|?' => 'b')
	));

	Assert::same('<select><option value="a">FIRST</option><option value="b">SECOND</option></select>', (string) Helpers::createSelectBox(
		array('a' => 'First', 'b' => 'Second'),
		NULL,
		new Translator
	));
});
