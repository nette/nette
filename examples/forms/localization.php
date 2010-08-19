<?php

/**
 * Nette\Forms localization example (with Zend_Translate).
 */


require_once __DIR__ . '/../../Nette/loader.php';

// set_include_path();
include_once 'Zend/Translate.php';

if (!class_exists('Zend_Translate')) {
	die('This example requires Zend Framework');
}

use Nette\Forms\Form,
	Nette\Debug,
	Nette\Web\Html;

Debug::enable();


class MyTranslator extends Zend_Translate implements Nette\ITranslator
{
	/**
	 * Translates the given string.
	 * @param  string   message
	 * @param  int      plural count
	 * @return string
	 */
	public function translate($message, $count = NULL)
	{
		return parent::translate($message);
	}
}


$countries = array(
	'Select your country',
	'Europe' => array(
		'CZ' => 'Czech Republic',
		'SK' => 'Slovakia',
	),
	'US' => 'USA',
	'?'  => 'other',
);

$sex = array(
	'm' => 'male',
	'f' => 'female',
);



// Step 1: Define form with validation rules
$form = new Form;
// enable translator
$translator = new MyTranslator('gettext', __DIR__ . '/messages.mo', 'cs');
$translator->setLocale('cs');
$form->setTranslator($translator);

// group Personal data
$form->addGroup('Personal data');
$form->addText('name', 'Your name:')
	->addRule(Form::FILLED, 'Enter your name');

$form->addText('age', 'Your age:')
	->addRule(Form::FILLED, 'Enter your age')
	->addRule(Form::INTEGER, 'Age must be numeric value')
	->addRule(Form::RANGE, 'Age must be in range from %d to %d', array(10, 100));

$form->addRadioList('gender', 'Your gender:', $sex);

$form->addText('email', 'E-mail:')
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

$form->addText('street', 'Street:');

$form->addText('city', 'City:')
	->addConditionOn($form['send'], Form::EQUAL, TRUE)
		->addRule(Form::FILLED, 'Enter your shipping address');

$form->addSelect('country', 'Country:', $countries)
	->skipFirst()
	->addConditionOn($form['send'], Form::EQUAL, TRUE)
		->addRule(Form::FILLED, 'Select your country');


// group Your account
$form->addGroup('Your account');

$form->addPassword('password', 'Choose password:')
	->addRule(Form::FILLED, 'Choose your password')
	->addRule(Form::MIN_LENGTH, 'The password is too short: it must be at least %d characters', 3);

$form->addPassword('password2', 'Reenter password:')
	->addConditionOn($form['password'], Form::VALID)
		->addRule(Form::FILLED, 'Reenter your password')
		->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);

$form->addFile('avatar', 'Picture:');

$form->addHidden('userid');

$form->addTextArea('note', 'Comment:');


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
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<title>Nette\Forms localization example | Nette Framework</title>

	<style type="text/css">
	html {
		font: 16px/1.5 sans-serif;
		border-top: 4.7em solid #F4EFE5;
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
		background: #EAF3FA;
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
	<h1>Nette\Forms localization example</h1>

	<?php echo $form ?>
</body>
</html>
