<?php

/**
 * Nette::Forms example 1
 *
 * - with separated rules definition
 * - using custom rendering
 */


require_once '../../Nette/loader.php';

/*use Nette::Forms::Form;*/
/*use Nette::Debug;*/

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



// Step 1: Define form
$form = new Form;
$form->addText('name', 'Your name:', 35);
$form->addText('age', 'Your age:', 5);
$form->addRadioList('gender', 'Your gender:', $sex);
$form->addText('email', 'E-mail:', 35)->setEmptyValue('@');

$form->addCheckbox('send', 'Ship to address');
$form->addText('street', 'Street:', 35);
$form->addText('city', 'City:', 35);
$form->addSelect('country', 'Country:', $countries)->skipFirst();

$form->addPassword('password', 'Choose password:', 20);
$form->addPassword('password2', 'Reenter password:', 20);
$form->addFile('avatar', 'Picture:');
$form->addHidden('userid');
$form->addTextArea('note', 'Comment:', 30, 5);

$form->addSubmit('submit1', 'Send');


// Step 1b: Define validation rules
$form['name']->addRule(Form::FILLED, 'Enter your name');

$form['age']->addRule(Form::FILLED, 'Enter your age');
$form['age']->addRule(Form::NUMERIC, 'Age must be numeric value');
$form['age']->addRule(Form::RANGE, 'Age must be in range from %.2f to %.2f', array(9.9, 100));

// conditional rule: if is email filled, ...
$form['email']->addCondition(Form::FILLED)
	->addRule(Form::EMAIL, 'Incorrect E-mail Address'); // ... then check email

// another conditional rule: if is checkbox checked...
$form['send']->addCondition(Form::EQUAL, TRUE)
	// toggle div #sendBox
	->toggle('sendBox')
	// ... add apply rules:
	->addRuleFor($form['city'], Form::FILLED, 'Enter your shipping address')
	->addRuleFor($form['country'], Form::FILLED, 'Select your country');

$form['password']->addRule(Form::FILLED, 'Choose your password');
$form['password']->addRule(Form::MIN_LENGTH, 'The password is too short: it must be at least %d characters', 3);

$form['password2']->addConditionOn($form['password'], Form::VALID)
	->addRule(Form::FILLED, 'Reenter your password')
	->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);





// Step 2: Check if form was submitted?
if ($form->isSubmitted()) {

	// Step 2c: Check if form is valid
	if ($form->isValid()) {
		echo '<h2>Form was submitted and successfully validated</h2>';

		$values = $form->getValues();
		Debug::dump($values);

		// this is the end, my friend :-)
		exit;
	}

} else {
	// not submitted, define default values
	$defaults = array(
		'name'    => 'John Doe',
		'userid'  => 231,
		'country' => 'CZ', // Czech Republic
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

	<title>Nette::Forms example 1 | Nette Framework</title>

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
	<h1>Nette::Forms example 1</h1>

	<?php $form->render('begin') ?>

	<?php if ($form->getErrors()): ?>
	<p>Opravte chyby:</p>
	<?php $form->render('errors') ?>
	<?php endif ?>

	<fieldset>
		<legend>Personal data</legend>
		<table>
		<tr class="required">
			<th><?php echo $form['name']->label ?></th>
			<td><?php echo $form['name']->control ?></td>
		</tr>
		<tr class="required">
			<th><?php echo $form['age']->label ?></th>
			<td><?php echo $form['age']->control ?></td>
		</tr>
		<tr>
			<th><?php echo $form['gender']->label ?></th>
			<td><?php echo $form['gender']->control ?></td>
		</tr>
		<tr>
			<th><?php echo $form['email']->label ?></th>
			<td><?php echo $form['email']->control ?></td>
		</tr>
		</table>
	</fieldset>


	<fieldset>
		<legend>Shipping address</legend>

		<p><?php echo $form['send']->control?><?=$form['send']->label ?></p>

		<table id="sendBox">
		<tr>
			<th><?php echo $form['street']->label ?></th>
			<td><?php echo $form['street']->control ?></td>
		</tr>
		<tr class="required">
			<th><?php echo $form['city']->label ?></th>
			<td><?php echo $form['city']->control ?></td>
		</tr>
		<tr class="required">
			<th><?php echo $form['country']->label ?></th>
			<td><?php echo $form['country']->control ?></td>
		</tr>
		</table>
	</fieldset>



	<fieldset>
		<legend>Your account</legend>
		<table>
		<tr class="required">
			<th><?php echo $form['password']->label ?></th>
			<td><?php echo $form['password']->control ?></td>
		</tr>
		<tr class="required">
			<th><?php echo $form['password2']->label ?></th>
			<td><?php echo $form['password2']->control ?></td>
		</tr>
		<tr>
			<th><?php echo $form['avatar']->label ?></th>
			<td><?php echo $form['avatar']->control ?></td>
		</tr>
		<tr>
			<th><?php echo $form['note']->label ?></th>
			<td><?php echo $form['note']->control ?></td>
		</tr>
		</table>
	</fieldset>

	<div>
		<?php echo $form['userid']->control ?>
		<?php echo $form['submit1']->control ?>
	</div>

	<?php $form->render('end'); ?>
</body>
</html>
