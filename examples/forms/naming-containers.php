<?php

/**
 * Nette\Forms example 6
 *
 * - using naming containers
 */


require '../../Nette/loader.php';

/*use Nette\Forms\Form;*/
/*use Nette\Debug;*/

Debug::enable();


$countries = array(
	'Select your country',
	'Europe' => array(
		'CZ' => 'Czech Republic',
		'FR' => 'France',
		'DE' => 'Germany',
		'GR' => 'Greece',
		'HU' => 'Hungary',
		'IE' => 'Ireland',
		'IT' => 'Italy',
		'NL' => 'Netherlands',
		'PL' => 'Poland',
		'SK' => 'Slovakia',
		'ES' => 'Spain',
		'CH' => 'Switzerland',
		'UA' => 'Ukraine',
		'GB' => 'United Kingdom',
	),
	'AU' => 'Australia',
	'CA' => 'Canada',
	'EG' => 'Egypt',
	'JP' => 'Japan',
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
$sub->addText('name', 'Your name:', 35);
$sub->addText('email', 'E-mail:', 35);
$sub->addText('street', 'Street:', 35);
$sub->addText('city', 'City:', 35);
$sub->addSelect('country', 'Country:', $countries);

// group Second person
$form->addGroup('Second person');
$sub = $form->addContainer('second');
$sub->addText('name', 'Your name:', 35);
$sub->addText('email', 'E-mail:', 35);
$sub->addText('street', 'Street:', 35);
$sub->addText('city', 'City:', 35);
$sub->addSelect('country', 'Country:', $countries);

// group for buttons
$form->addGroup();

$form->addSubmit('submit1', 'Send');






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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-language" content="en" />

	<title>Nette\Forms example 6 | Nette Framework</title>

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
</head>

<body>
	<h1>Nette\Forms example 6</h1>

	<?php echo $form ?>
</body>
</html>
