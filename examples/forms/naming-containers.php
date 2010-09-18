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

		Debug::dump($form->values);

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
	<link rel="stylesheet" type="text/css" media="screen" href="files/style.css" />
	</style>
</head>

<body>
	<h1>Nette\Forms naming containers example</h1>

	<?php echo $form ?>
</body>
</html>
