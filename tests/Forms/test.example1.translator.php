<h1>Nette::Forms example 1 &amp; translator</h1>

<?php

require_once '../../Nette/loader.php';
require_once 'Zend/Translate.php';

/*use Nette::Forms::Form;*/
/*use Nette::Debug;*/

Debug::enable();


class MyTranslator extends Zend_Translate implements /*Nette::*/ITranslator
{

    /**
     * Translates the given string.
     * @param  string   message
     * @param  int      plural
     * @return string
     */
    public function translate($message, $plural = NULL)
    {
    	return parent::translate($message);
    }

}


$countries = array(
	'Select your country',
	'Europe' => array(
		1 => 'Czech Republic',
		2 => 'Slovakia',
	),
	3 => 'USA',
	4 => 'other',
);

$sex = array(
	'm' => 'male',
	'f' => 'female',
);



// ******* DEFINE FORM
$form = new Form();
$form->addText('name', 'Your name:', 35);  // item name, label, size, maxlength
$form->addText('age', 'Your age:', 5);
$form->addRadioList('gender', 'Your gender:', $sex);
$form->addText('email', 'E-Mail:', 35)->emptyValue = '@';

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


// define form rules
$form['name']->addRule(Form::FILLED, 'Enter your name');
$form['age']->addRule(Form::FILLED, 'Enter your age');
$form['age']->addRule(Form::NUMERIC, 'Age must be numeric value');
$form['age']->addRule(Form::RANGE, 'Age must be in range from %d to %d', array(10, 100));

// conditional rule: if is email filled, ...
$cond = $form['email']->addCondition(Form::FILLED);
// ... then check email
$cond->addRuleFor($form['email'], Form::EMAIL, 'Incorrect E-mail Address');

// another conditional rule: if is checkbox checked...
$cond = $form['send']->addCondition(Form::EQUAL, TRUE)->toggle('sendBox');
// ... add rules:
$cond->addRuleFor($form['city'], Form::FILLED, 'Enter your shipping address');
$cond->addRuleFor($form['country'], Form::FILLED, 'Select your country');

$form['password']->addRule(Form::FILLED, 'Choose your password');
$form['password']->addRule(Form::MIN_LENGTH, 'The password is too short: it must be at least %d characters', 3);
$form['password2']->addRule(Form::FILLED, 'Reenter your password');
// special case: argument is another form item
$form['password2']->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);


$translator = new MyTranslator('gettext', 'messages.mo');
$translator->setLocale('cs');
$form->setTranslator($translator);
// now form is defined






// ******* FORM FILLING LOOP

// was form submitted?
if ($form->isSubmitted()) {
	echo '<h2>Submitted</h2>';

	// check validation
	if ($form->isValid()) {
		echo '<h2>And successfully validated!</h2>';

		$values = $form->getValues();
		Debug::dump($values);

		// this is the end :-)
		exit;
	}

} else { // not submitted?

	// so define default values
	$defaults = array(
		'name'    => 'John Doe',
		'userid'  => 12345,
		'country' => 1, // Czech Republic
	);

	$form->setDefaults($defaults);
}



echo '<style> .required { color: darkred; } </style>';

$form->renderBegin();
if ($form->getErrors()) {
	echo '<p>', $translator->translate('Please correct the errors:'), '</p>';
	echo $form->renderErrors();
}
?>

<fieldset>
<legend><?=$translator->translate('Personal data')?></legend>
<table>
<tr class="required">
	<th><?=$form['name']->label?></th>
	<td><?=$form['name']->control?></td>
</tr>
<tr class="required">
	<th><?=$form['age']->label?></th>
	<td><?=$form['age']->control?></td>
</tr>
<tr>
	<th><?=$form['gender']->label?></th>
	<td><?=$form['gender']->control?></td>
</tr>
<tr>
	<th><?=$form['email']->label?></th>
	<td><?=$form['email']->control?></td>
</tr>
</table>
</fieldset>



<fieldset>
<legend><?=$translator->translate('Shipping address')?></legend>

<p><?=$form['send']->control?><?=$form['send']->label?></p>

<table id="sendBox">
<tr>
	<th><?=$form['street']->label?></th>
	<td><?=$form['street']->control?></td>
</tr>
<tr class="required">
	<th><?=$form['city']->label?></th>
	<td><?=$form['city']->control?></td>
</tr>
<tr class="required">
	<th><?=$form['country']->label?></th>
	<td><?=$form['country']->control?></td>
</tr>
</table>
</fieldset>



<fieldset>
<legend><?=$translator->translate('Your account')?></legend>
<table>
<tr class="required">
	<th><?=$form['password']->label?></th>
	<td><?=$form['password']->control?></td>
</tr>
<tr class="required">
	<th><?=$form['password2']->label?></th>
	<td><?=$form['password2']->control?></td>
</tr>
<tr>
	<th><?=$form['avatar']->label?></th>
	<td><?=$form['avatar']->control?></td>
</tr>
<tr>
	<th><?=$form['note']->label?></th>
	<td><?=$form['note']->control?></td>
</tr>
</table>
</fieldset>

<div>
<?=$form['userid']->control?>
<?=$form['submit1']->control?>
</div>

<? $form->renderEnd(); ?>


<?
Debug::dump($form->getValues());

echo '</pre>';
