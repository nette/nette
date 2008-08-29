<h1>Nette::Forms user validator test</h1>

<?php

require_once '../../Nette/loader.php';

/*use Nette::Forms::Form;*/
/*use Nette::Debug;*/

Debug::enable();


function myValidator1($item, $arg)
{
	return $item->getValue() != $arg;
}


$form = new Form();
$form->addText('name', 'Text:', 10)
	->addRule('myValidator1', 'Value %d is not allowed!', 11)
	->addRule(~'myValidator1', 'Value %d is required!', 22);

$form->addSubmit('submit1', 'Send');


// was form submitted?
if ($form->isSubmitted()) {
	echo '<h2>Submitted</h2>';

	// check validation
	if ($form->isValid()) {
		echo '<h2>And successfully validated!</h2>';

		$values = $form->getValues();
		Debug::dump($values);

		// this is the end :-)
		exit;
	}

} else { // not submitted?

	// so define default values
	$defaults = array(
	);

	$form->setDefaults($defaults);
}

$form->render();
