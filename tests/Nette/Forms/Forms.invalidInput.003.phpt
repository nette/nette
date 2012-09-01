<?php

/**
 * Test: Nette\Forms invalid input - invalid characters.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 * @subpackage UnitTests
 *
 */

use Nette\Http,
	Nette\Forms\Form,
	Nette\ArrayHash;



require __DIR__ . '/../bootstrap.php';



if (PHP_VERSION_ID >= 50400 && ICONV_IMPL === 'glibc') {
	TestHelpers::skip('Buggy iconv in PHP');
}



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'name' => "invalid\xAA\xAA\xAAutf",
	'note' => "invalid\xAA\xAA\xAAutf",
	'userid' => "invalid\xAA\xAA\xAAutf",
);

$form = new Form();
$form->addText('name', 'Your name:', 35);  // item name, label, size, maxlength
$form->addTextArea('note', 'Comment:', 30, 5);
$form->addHidden('userid');

$form->addSubmit('submit1', 'Send');

Assert::true( (bool) $form->isSubmitted() );
Assert::equal( ArrayHash::from(array(
	'name' => 'invalidutf',
	'note' => 'invalidutf',
	'userid' => 'invalidutf',
), FALSE), $form->getValues() );
