<?php

/**
 * Nette\Forms and HTML5.
 *
 * - for the best experience, use the latest version of browser (Internet Explorer 9, Firefox 4, Chrome 5, Safari 5, Opera 9)
 */


require_once '../../Nette/loader.php';

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

$form->addSubmit('submit1', 'Send');



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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-language" content="en" />

	<title>Nette\Forms and HTML5 | Nette Framework</title>

	<style type="text/css">
	<!--
	.required {
		color: darkred
	}

	fieldset {
		padding: .5em;
		margin: .3em 0;
		background: #EAF3FA;
		border: 1px solid #b2d1eb;
	}

	input.button {
		font-size: 120%;
	}

	th {
		width: 8em;
		text-align: right;
	}
	-->
	</style>

	<script src="netteForms.js"></script>
</head>

<body>
	<h1>Nette\Forms and HTML5</h1>

	<?php echo $form ?>
</body>
</html>
