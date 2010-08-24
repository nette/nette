<?php

/**
 * Nette\Forms custom validator example.
 */


require_once __DIR__ . '/../../Nette/loader.php';

use Nette\Forms\Form,
	Nette\Debug;

Debug::enable();



// Step 0: Define custom validator
function myValidator($item, $arg)
{
	return $item->getValue() % $arg === 0;
}



// Step 1: Define form with validation rules
$form = new Form;

$form->addText('num1', 'Multiple of 8:')
	->addRule('myValidator', 'First number must be %d multiple', 8);

$form->addText('num2', 'Not multiple of 5:')
	->addRule(~'myValidator', 'Second number must not be %d multiple', 5); // negative


$form->addSubmit('submit', 'Send');



// Step 2: Check if form was submitted?
if ($form->isSubmitted()) {

	// Step 2c: Check if form is valid
	if ($form->isValid()) {
		echo '<h2>Form was submitted and successfully validated</h2>';

		$values = $form->getValues();
		Debug::dump($values);

		// this is the end, my friend :-)
		if (empty($disableExit)) exit;
	}

} else {
	// not submitted, define default values
	$defaults = array(
		'num1'    => '5',
		'num2'    => '5',
	);

	$form->setDefaults($defaults);
}



// Step 3: Render form
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<title>Nette\Forms custom validator example | Nette Framework</title>

	<style type="text/css">
	html {
		font: 16px/1.5 sans-serif;
		border-top: 4.7em solid #F4EBDB;
	}

	body {
		max-width: 990px;
		margin: -4.7em auto 0;
		background: white;
		color: #333;
	}

	h1 {
		font-size: 1.9em;
		margin: .5em 0 1.5em;
		background: url(http://files.nette.org/icons/logo-e1.png) right center no-repeat;
		color: #7A7772;
		text-shadow: 1px 1px 0 white;
	}

	.required {
		color: darkred
	}

	fieldset {
		padding: .5em;
		margin: .5em 0;
		background: #E4F1FC;
		border: 1px solid #B2D1EB;
	}

	input.button {
		font-size: 120%;
	}

	th {
		width: 10em;
		text-align: right;
	}
	</style>
</head>

<body>
	<h1>Nette\Forms custom validator example</h1>

	<?php echo $form ?>
</body>
</html>
