<h1>Nette::Forms naming container test</h1>

<?php

require_once '../../Nette/loader.php';

/*use Nette::ComponentContainer;*/
/*use Nette::Forms::Form;*/
/*use Nette::Forms::TextInput;*/
/*use Nette::Forms::FormContainer;*/
/*use Nette::Debug;*/

Debug::enable();


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

$form->addText('age', 'Your age:', 5);

$form->addSubmit('submit1', 'Send');


$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array (
	'name' => 'jim',
	'text1' => 'hello',
	'text2' => 'world',
	'formCont' =>
	array (
		'name' => 'jack',
		'age' => '23',
	),
	'firstperson' =>
	array (
		'name' => 'david',
		'age' => '30',
	),
	'secondperson' =>
	array (
		'name' => 'jim',
		'age' => '40',
	),
	'age' => '50',
);


echo "Submitted?\n";
Debug::dump(gettype($form->isSubmitted()));


echo "Valid?\n";
Debug::dump($form->isValid());

echo "Values:\n";
Debug::dump($form->getValues());


$defaults = array (
	'name' => 'jim',
	'text1' => 'hello',
	'text2' => 'world',
	'formCont' =>
	array (
		'name' => 'jack',
		'age' => '23',
	),
	'firstperson' =>
	array (
		'name' => 'david',
		'age' => '30',
	),
	'secondperson' =>
	array (
		'name' => 'jim',
		'age' => '40',
	),
	'age' => '50',
);

$form->setDefaults($defaults);
echo "Setted values:\n";
Debug::dump($form->getValues());


echo "Render:\n";
echo $form;
