<?php

/**
 * Test: Nette\Forms naming container.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\ComponentContainer,
	Nette\Forms\Form,
	Nette\Forms\TextInput,
	Nette\Forms\FormContainer;



require __DIR__ . '/../NetteTest/initialize.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'name' => 'jim',
	'text1' => 'hello',
	'text2' => 'world',
	'formCont' =>
	array(
		'name' => 'jack',
		'age' => '23',
	),
	'firstperson' =>
	array(
		'name' => 'david',
		'age' => '30',
	),
	'secondperson' =>
	array(
		'name' => 'jim',
		'age' => '40',
	),
	'age' => '50',
);

$_FILES = array(
	'secondperson' => array(
		'name' => array(
			'avatar' => 'license.txt',
		),

		'type' => array(
			'avatar' => 'text/plain',
		),

		'tmp_name' => array(
			'avatar' => 'C:\\PHP\\temp\\php1D5C.tmp',
		),

		'error' => array(
			'avatar' => 0,
		),

		'size' => array(
			'avatar' => 3013,
		),
	),
);



$form = new Form();
$form->addText('name', 'Your name:', 35);

$sub = new ComponentContainer($form, 'container');
$sub->addComponent(new TextInput('First line'), 'text1');
$sub->addComponent(new TextInput('Second line'), 'text2');
$sub->addComponent($sub2 = new FormContainer, 'formCont');
	$sub2->addText('name', 'Your name:', 35);
	$sub2->addText('age', 'Your age:', 5);

$sub = $form->addContainer('firstperson');
$sub->addText('name', 'Your name:', 35);
$sub->addText('age', 'Your age:', 5);

$sub = $form->addContainer('secondperson');
$sub->addText('name', 'Your name:', 35);
$sub->addText('age', 'Your age:', 5);
$sub->addFile('avatar', 'Picture:');

$form->addText('age', 'Your age:', 5);

$form->addSubmit('submit1', 'Send');

dump( (bool) $form->isSubmitted() );
dump( $form->getValues() );



__halt_compiler() ?>

------EXPECT------
bool(TRUE)

array(7) {
	"name" => string(3) "jim"
	"text1" => string(5) "hello"
	"text2" => string(5) "world"
	"formCont" => array(2) {
		"name" => string(4) "jack"
		"age" => string(2) "23"
	}
	"firstperson" => array(2) {
		"name" => string(5) "david"
		"age" => string(2) "30"
	}
	"secondperson" => array(3) {
		"name" => string(3) "jim"
		"age" => string(2) "40"
		"avatar" => object(%ns%HttpUploadedFile) (5) {
			"name" private => string(11) "license.txt"
			"type" private => NULL
			"size" private => int(3013)
			"tmpName" private => string(23) "C:\PHP\temp\php1D5C.tmp"
			"error" private => int(0)
		}
	}
	"age" => string(2) "50"
}
