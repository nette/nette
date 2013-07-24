<?php

/**
 * Test: Nette\Forms\Helpers::extractHttpData()
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form,
	Nette\Forms\Helpers;


require __DIR__ . '/../bootstrap.php';


test(function() { // non-multiple
	Assert::same('jim', Helpers::extractHttpData(array('name' => 'jim'), 'name', Form::DATA_LINE));
	Assert::same('jim', Helpers::extractHttpData(array('name' => 'jim'), 'name', Form::DATA_TEXT));

	Assert::same('jim', Helpers::extractHttpData(array(
		'first' => array('name' => 'jim'),
	), 'first[name]', Form::DATA_LINE));

	Assert::same('0', Helpers::extractHttpData(array('zero' => '0'), 'zero', Form::DATA_LINE));
	Assert::same('', Helpers::extractHttpData(array('empty' => ''), 'empty', Form::DATA_LINE));

	Assert::null( Helpers::extractHttpData(array(), 'missing', Form::DATA_LINE));
	Assert::null( Helpers::extractHttpData(array('invalid' => '1'), 'invalid[name]', Form::DATA_LINE));
	Assert::null( Helpers::extractHttpData(array('invalid' => array('')), 'invalid', Form::DATA_LINE));
	Assert::null( Helpers::extractHttpData(array('invalid' => array('')), 'invalid', Form::DATA_TEXT));

	Assert::same('a  b   c', Helpers::extractHttpData(array('text' => "  a\r b \n c "), 'text', Form::DATA_LINE));
	Assert::same("  a\n b \n c ", Helpers::extractHttpData(array('text' => "  a\r b \n c "), 'text', Form::DATA_TEXT));
});


test(function() { // multiple
	Assert::same(array('1', '2'), Helpers::extractHttpData(array('multi' => array('1', '2')), 'multi[]', Form::DATA_LINE));
	Assert::same(array('1', '2'), Helpers::extractHttpData(array('multi' => array('1', '2')), 'multi[]', Form::DATA_TEXT));
	Assert::same(array('1', '2'), Helpers::extractHttpData(array('multi' => array('x' => '1', 2 => '2')), 'multi[]', Form::DATA_TEXT));

	Assert::same(array('3', '4'), Helpers::extractHttpData(array(
		'container' => array('image' => array('3', '4')),
	), 'container[image][]', Form::DATA_LINE));

	Assert::same(array('0'), Helpers::extractHttpData(array('zero' => array(0)), 'zero[]', Form::DATA_LINE));
	Assert::same(array(''), Helpers::extractHttpData(array('empty' => array('')), 'empty[]', Form::DATA_LINE));

	Assert::same(array(), Helpers::extractHttpData(array(), 'missing[]', Form::DATA_LINE));
	Assert::same(array(), Helpers::extractHttpData(array('invalid' => 'red-dwarf'), 'invalid[]', Form::DATA_LINE));
	Assert::same(array(), Helpers::extractHttpData(array('invalid' => array(array(''))), 'invalid[]', Form::DATA_LINE));

	Assert::same(array('a  b   c'), Helpers::extractHttpData(array('text' => array("  a\r b \n c ")), 'text[]', Form::DATA_LINE));
	Assert::same(array("  a\n b \n c "), Helpers::extractHttpData(array('text' => array("  a\r b \n c ")), 'text[]', Form::DATA_TEXT));
});


test(function() { // files
	$file = new Nette\Http\FileUpload(array(
		'name' => 'license.txt',
		'type' => NULL,
		'size' => 3013,
		'tmpName' => 'tmp',
		'error' => 0,
	));

	Assert::equal($file, Helpers::extractHttpData(array('avatar' => $file), 'avatar', Form::DATA_FILE));

	Assert::null( Helpers::extractHttpData(array(), 'missing', Form::DATA_FILE));
	Assert::null( Helpers::extractHttpData(array('invalid' => NULL), 'invalid', Form::DATA_FILE));
	Assert::null( Helpers::extractHttpData(array('invalid' => array(NULL)), 'invalid', Form::DATA_FILE));
	Assert::null( Helpers::extractHttpData(array(
		'multiple' => array('avatar' => array($file, $file)),
	), 'multiple[avatar]', Form::DATA_FILE));


	Assert::equal(array($file, $file), Helpers::extractHttpData(array(
		'multiple' => array('avatar' => array($file, $file)),
	), 'multiple[avatar][]', Form::DATA_FILE));

	Assert::same(array(), Helpers::extractHttpData(array(), 'missing[]', Form::DATA_FILE));
	Assert::same(array(), Helpers::extractHttpData(array('invalid' => NULL), 'invalid[]', Form::DATA_FILE));
	Assert::same(array(), Helpers::extractHttpData(array('invalid' => $file), 'invalid[]', Form::DATA_FILE));
	Assert::same(array(), Helpers::extractHttpData(array('invalid' => array(NULL)), 'invalid[]', Form::DATA_FILE));
});
