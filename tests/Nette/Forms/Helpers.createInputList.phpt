<?php

/**
 * Test: Nette\Forms\Helpers::createInputList()
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Helpers,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function() {
	Assert::same(
		'',
		Helpers::createInputList(array())
	);

	Assert::same(
		'<label><input value="0">a</label>',
		Helpers::createInputList(array('a'))
	);

	Assert::same(
		'<label><input value="a">First</label><label><input value="b">Second</label>',
		Helpers::createInputList(
			array('a' => 'First', 'b' => 'Second')
		)
	);

	Assert::same(
		'<label class="button"><input type="checkbox" value="a">First</label><label class="button"><input type="checkbox" value="b">Second</label>',
		Helpers::createInputList(
			array('a' => 'First', 'b' => 'Second'),
			array('type' => 'checkbox'),
			array('class' => 'button')
		)
	);

	Assert::same(
		'<label style="color:blue" class="class1 class2"><input title="Hello" type="checkbox" checked value="a">First</label><label style="color:blue"><input title="Hello" type="radio" value="b">Second</label>',
		Helpers::createInputList(
			array('a' => 'First', 'b' => 'Second'),
			array(
				'type:' => array('a' => 'checkbox', 'b' => 'radio'),
				'checked?' => array('a'),
				'title' => 'Hello',
			),
			array(
				'class:' => array('a' => array('class1', 'class2')),
				'style' => array('color' => 'blue'),
			)
		)
	);

	Assert::same(
		'<label><input value="a">First</label><br><label><input value="b">Second</label>',
		Helpers::createInputList(
			array('a' => 'First', 'b' => 'Second'),
			NULL,
			NULL,
			'<br>'
		)
	);

	Assert::same(
		'<div><label><input value="a">First</label></div><div><label><input value="b">Second</label></div>',
		Helpers::createInputList(
			array('a' => 'First', 'b' => 'Second'),
			NULL,
			NULL,
			Html::el('div')
		)
	);

	Assert::same(
		'<label><input value="a">First</label><label><input value="b">Second</label>',
		Helpers::createInputList(
			array('a' => 'First', 'b' => 'Second'),
			NULL,
			NULL,
			Html::el(NULL)
		)
	);
});
