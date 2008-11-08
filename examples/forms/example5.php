<?php

/**
 * Nette::Forms example 5
 *
 * - custom validator usage
 */


require_once '../../Nette/loader.php';

/*use Nette::Forms::Form;*/
/*use Nette::Debug;*/

Debug::enable(E_ALL | E_STRICT);



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
	// not submitted, define default values
	$defaults = array(
		'num1'    => '5',
		'num2'    => '5',
	);

	$form->setDefaults($defaults);
}




// Step 3: Render form
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-language" content="en" />

	<title>Nette::Forms example 5 | Nette Framework</title>

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
	<h1>Nette::Forms example 5</h1>

	<?php echo $form ?>
</body>
</html>
