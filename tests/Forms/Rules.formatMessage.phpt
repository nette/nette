<?php

/**
 * Test: Nette\Forms\Rules::validateMessage()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

/*use Nette\Forms\TextBase;*/
/*use Nette\Forms\TextInput;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$form = new Form;
$form->addText('email', 'E-mail')
	->addRule(Form::EMAIL, '%label %value is invalid [field %name]')
    ->setDefaultValue('xyz');

echo $form;

$form->validate();

dump( $form->getErrors() );



__halt_compiler();

------EXPECT------
%A%
nette.forms["frm-"] = {
	validators: {
		"email": function(sender) {
			var res, val, form = sender.form || sender;
			res = /^[^@\s]+@[^@\s]+\.[a-z]{2,10}$/i.test(val = nette.getValue(form["email"]));
			if (!res) return "E-mail %value is invalid [field email]".replace('%value', val);
			;
		}
	},
%A%

array(1) {
	0 => string(35) "E-mail xyz is invalid [field email]"
}
