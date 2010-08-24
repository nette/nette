<?php

/**
 * Nette\Forms and HTML5.
 *
 * - for the best experience, use the latest version of browser (Internet Explorer 9, Firefox 4, Chrome 5, Safari 5, Opera 9)
 */


require_once __DIR__ . '/../../Nette/loader.php';

use Nette\Forms\Form,
	Nette\Debug;

Debug::enable();


// Step 1: Define form with validation rules
$form = new Form;

$form->addGroup();

$form->addText('query', 'Search:')
	->setType('search')
	->setAttribute('autofocus');

$form->addText('count', 'Number of results:')
	->setType('number')
	->setDefaultValue(10)
	->addRule(Form::INTEGER, 'Must be numeric value')
	->addRule(Form::RANGE, 'Must be in range from %d to %d', array(1, 100));

$form->addText('precision', 'Precision:')
	->setType('range')
	->setDefaultValue(50)
	->addRule(Form::INTEGER, 'Precision must be numeric value')
	->addRule(Form::RANGE, 'Precision must be in range from %d to %d', array(0, 100));

$form->addText('email', 'Send to e-mail:')
	->setType('email')
	->setAttribute('autocomplete', 'off')
	->setAttribute('placeholder', 'Optional, but Recommended')
	->addCondition(Form::FILLED) // conditional rule: if is email filled, ...
		->addRule(Form::EMAIL, 'Incorrect E-mail Address'); // ... then check email

$form->addSubmit('submit', 'Send');



// Step 2: Check if form was submitted?
if ($form->isSubmitted() && $form->isValid()) {
	echo '<h2>Form was submitted and successfully validated</h2>';

	$values = $form->getValues();
	Debug::dump($values);

	// this is the end, my friend :-)
	if (empty($disableExit)) exit;
}



// Step 3: Render form
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<title>Nette\Forms and HTML5 | Nette Framework</title>

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

	<script src="netteForms.js"></script>
</head>

<body>
	<h1>Nette\Forms and HTML5</h1>

	<?php echo $form ?>
</body>
</html>
