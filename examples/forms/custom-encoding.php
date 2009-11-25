<?php

/**
 * Nette\Forms custom encoding example.
 *
 * - Forms internally works in UTF-8 encoding!
 */


require '../../Nette/loader.php';

/*use Nette\Forms\Form;*/
/*use Nette\Debug;*/
/*use Nette\Web\Html;*/

Debug::enable();


$countries = array(
	'Select your country',
	'Europe' => array(
		'CZ' => 'Česká republika',
		'SK' => 'Slovakia',
		'GB' => 'United Kingdom',
	),
	'CA' => 'Canada',
	'US' => 'United States',
	'?'  => 'other',
);



// Step 1: Define form with validation rules
$form = new Form;
$form->encoding = 'ISO-8859-1';

// group Personal data
$form->addGroup('Personal data');
$form->addText('name', 'Your name:', 35);

$form->addMultiSelect('country', 'Country:')
	->skipFirst()
	->setItems($countries, FALSE);

$form->addHidden('userid');

$form->addTextArea('note', 'Comment:', 30, 5);


// group for buttons
$form->addGroup();

$form->addSubmit('submit1', 'Send');






// Step 2: Check if form was submitted?
if ($form->isSubmitted()) {

	// Step 2c: Check if form is valid
	if ($form->isValid()) {
		header('Content-type: text/html; charset=utf-8');

		echo '<h2>Form was submitted and successfully validated</h2>';

		$values = $form->getValues();
		Debug::dump($values);

		// this is the end, my friend :-)
		if (empty($disableExit)) exit;
	}

} else {
	// not submitted, define default values
	$defaults = array(
		'name'    => 'Žluťoučký kůň',
		'userid'  => 'kůň',
		'note' => 'жед',
		'country' => 'Česká republika', // Czech Republic
	);

	$form->setDefaults($defaults);
}



// Step 3: Render form
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo $form->encoding ?>" />
	<meta http-equiv="content-language" content="en" />

	<title>Nette\Forms custom encoding example | Nette Framework</title>

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
	<h1>Nette\Forms custom encoding example</h1>

	<?php echo $form ?>
</body>
</html>
