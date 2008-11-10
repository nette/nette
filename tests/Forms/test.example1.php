<h1>Nette\Forms example 1</h1>

<?php

require_once '../../Nette/loader.php';

/*use Nette\Forms\Form;*/
/*use Nette\Debug;*/

Debug::enable();

// ******* DEFINE FORM
$form = new Form;
$form->addText('name', 'Your name:');
$form->addText('age', 'Your age:', 5);
$form->addFile('avatar', 'Picture:');
$form->addSubmit('submit1', 'Send');


// define form rules
$form['age']->addRule(Form::RANGE, 'Age must be in range from %.2f to %.2f', array(9.9, 100));

$form['avatar']->addRule(Form::MIME_TYPE, 'Avatar must be image', 'image/*');
$form['avatar']->addError('User error');
$form['avatar']->addError(/*Nette\Web\*/Html::el('strong', 'User error #2'));


echo $form;
