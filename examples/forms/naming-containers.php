<?php

/**
 * Nette\Forms naming containers example.
 *
 * - using naming containers
 */


require_once __DIR__ . '/../../Nette/loader.php';

use Nette\Forms\Form,
	Nette\Debug;

Debug::enable();


$countries = array(
	'Select your country',
	'Europe' => array(
		'CZ' => 'Czech Republic',
		'SK' => 'Slovakia',
		'GB' => 'United Kingdom',
	),
	'CA' => 'Canada',
	'US' => 'United States',
	'?'  => 'other',
);

$sex = array(
	'm' => 'male',
	'f' => 'female',
);



// Step 1: Define form with validation rules
$form = new Form;

// group First person
$form->addGroup('First person');
$sub = $form->addContainer('first');
$sub->addText('name', 'Your name:');
$sub->addText('email', 'E-mail:');
$sub->addText('street', 'Street:');
$sub->addText('city', 'City:');
$sub->addSelect('country', 'Country:', $countries);

// group Second person
$form->addGroup('Second person');
$sub = $form->addContainer('second');
$sub->addText('name', 'Your name:');
$sub->addText('email', 'E-mail:');
$sub->addText('street', 'Street:');
$sub->addText('city', 'City:');
$sub->addSelect('country', 'Country:', $countries);

// group for buttons
$form->addGroup();

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
}



// Step 3: Render form
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<title>Nette\Forms naming containers example | Nette Framework</title>

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
	<h1>Nette\Forms naming containers example</h1>

	<?php echo $form ?>
</body>
</html>
