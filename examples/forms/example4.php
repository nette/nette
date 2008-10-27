<?php

/**
 * Nette::Forms example 4
 *
 * - custom form rendering
 */


require_once '../../Nette/loader.php';

/*use Nette::Forms::Form;*/
/*use Nette::Debug;*/
/*use Nette::Web::Html;*/

Debug::enable(E_ALL | E_STRICT);


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
// setup custom rendering
$renderer = $form->getRenderer();
$renderer->wrappers['form']['container'] = Html::el('div')->id('form');
$renderer->wrappers['form']['errors'] = FALSE;
$renderer->wrappers['group']['container'] = NULL;
$renderer->wrappers['group']['label'] = 'h3';
$renderer->wrappers['pair']['container'] = NULL;
$renderer->wrappers['controls']['container'] = 'dl';
$renderer->wrappers['control']['container'] = 'dd';
$renderer->wrappers['control']['.odd'] = 'odd';
$renderer->wrappers['control']['errors'] = TRUE;
$renderer->wrappers['label']['container'] = 'dt';
$renderer->wrappers['label']['suffix'] = ':';


// group Personal data
$form->addGroup('Personal data');
$form->addText('name', 'Your name', 35)
	->addRule(Form::FILLED, 'Enter your name');

$form->addText('age', 'Your age', 5)
	->addRule(Form::FILLED, 'Enter your age')
	->addRule(Form::INTEGER, 'Age must be numeric value')
	->addRule(Form::RANGE, 'Age must be in range from %.2f to %.2f', array(9.9, 100));

$form->addRadioList('gender', 'Your gender', $sex);

$form->addText('email', 'E-mail', 35)
	->setEmptyValue('@')
	->addCondition(Form::FILLED) // conditional rule: if is email filled, ...
		->addRule(Form::EMAIL, 'Incorrect E-mail Address'); // ... then check email


// group Shipping address
$form->addGroup('Shipping address')
	->setOption('embedNext', TRUE);

$form->addCheckbox('send', 'Ship to address')
	->addCondition(Form::EQUAL, TRUE) // conditional rule: if is checkbox checked...
		->toggle('sendBox'); // toggle div #sendBox


// subgroup
$form->addGroup()
	->setOption('container', Html::el('div')->id('sendBox'));

$form->addText('street', 'Street', 35);

$form->addText('city', 'City', 35)
	->addConditionOn($form['send'], Form::EQUAL, TRUE)
		->addRule(Form::FILLED, 'Enter your shipping address');

$form->addSelect('country', 'Country', $countries)
	->skipFirst()
	->addConditionOn($form['send'], Form::EQUAL, TRUE)
		->addRule(Form::FILLED, 'Select your country');


// group Your account
$form->addGroup('Your account');

$form->addPassword('password', 'Choose password', 20)
	->addRule(Form::FILLED, 'Choose your password')
	->addRule(Form::MIN_LENGTH, 'The password is too short: it must be at least %d characters', 3)
	->setOption('description', '(at least 3 characters)');

$form->addPassword('password2', 'Reenter password', 20)
	->addConditionOn($form['password'], Form::VALID)
		->addRule(Form::FILLED, 'Reenter your password')
		->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);

$form->addFile('avatar', 'Picture');

$form->addHidden('userid');

$form->addTextArea('note', 'Comment', 30, 5);


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

	<title>Nette::Forms example 4 | Nette Framework</title>

	<style type="text/css">
	<!--
	body {
		font-family: "Trebuchet MS", "Geneva CE", lucida, sans-serif;
	}

	.required {
		font-weight: bold;
	}

	.error {
		color: red;
	}

	input.text {
		border: 1px solid #78bd3f;
		padding: 3px;
		color: black;
		background: white;
	}

	input.button {
		font-size: 120%;
	}

	dt, dd {
		padding: .5em 1em;
	}

	#form {
		width: 550px;
	}

	#form h3 {
		background: #78bd3f;
		color: white;
		margin: 0;
		padding: .1em 1em;
		font-size: 100%;
		font-weight: normal;
		clear: both;
	}

	#form dl {
		background: #F8F8F8;
		margin: 0;
	}

	#form dt {
		text-align: right;
		font-weight: normal;
		float: left;
		width: 10em;
		clear: both;
	}

	#form dd {
		margin: 0;
		padding-left: 10em;
		display: block;
	}

	#form dd ul {
		list-style: none;
		font-size: 90%;
	}

	#form dd.odd {
		background: #EEE;
	}
	-->
	</style>
</head>

<body>
	<h1>Nette::Forms example 4</h1>

	<?php echo $form ?>
</body>
</html>
