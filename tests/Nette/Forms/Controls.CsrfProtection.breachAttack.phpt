<?php

/**
 * Test: Nette\Forms\Controls\CsrfProtection and BREACH attack.
 *
 * @author     Jan-Sebastian Fabík
 */

use Nette\Forms\Controls\CsrfProtection,
	Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form;

$input = $form->addProtection('Security token did not match. Possible CSRF attack.');

$target = strlen($input->getControl()->value);

$charlist = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

$strings = array();
for ($a = 0; $a < 65; $a++) {
	for ($b = 0; $b < 65; $b++) {
		$strings[] = $charlist[$a] . $charlist[$b];
	}
}

for ($i = 3; $i <= $target; $i++) {
	$code = (string) $input->getControl();
	$shortest = NULL;
	$newStrings = array();
	foreach ($strings as $string) {
		for ($j = 0; $j < 65; $j++) {
			$s = $string . $charlist[$j];
			$length = strlen(gzdeflate($code . '<input type="text" value="' . $s . '">'));
			if ($shortest === NULL || $length < $shortest) {
				$shortest = $length;
				$newStrings = array();
			}
			if ($shortest === $length) {
				$newStrings[] = $s;
			}
		}
	}
	$strings = $newStrings;
}

foreach ($strings as $string) {
	$input->setValue($string);
	Assert::false(CsrfProtection::validateCsrf($input));
}
