<?php

/**
 * Test: Nette\Forms\Rules::validateMessage()
 *
 * @author     David Grudl
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$form = new Form;
$form->addText('email', 'E-mail')
	->addRule(Form::EMAIL, '%label %value is invalid [field %name]')
	->setDefaultValue('xyz');

Assert::match('%A%
nette.forms["frm-"] = {
	validators: {
		"email": function(sender) {
			var res, val, form = sender.form || sender;
			res = /^[^@\s]+@[^@\s]+\.[a-z]{2,10}$/i.test(val = nette.getValue(form["email"]));
			if (!res) return "E-mail %value is invalid [field email]".replace(\'%value\', val);
			;
		}
	},
%A%', (string) $form);

$form->validate();

Assert::same( array(
	"E-mail xyz is invalid [field email]",
), $form->getErrors() );
