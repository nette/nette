<?php

/**
 * Test: Nette\Forms HTTP data.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'GET';

$_POST = $_FILES = array();
$_GET = array('item');

test(function() {
	$form = new Form;
	$form->setMethod($form::GET);
	$form->addSubmit('send', 'Send');

	Assert::true( (bool) $form->isSubmitted() );
	Assert::same( array('item'), $form->getHttpData() );
	Assert::same( array(), $form->getValues(TRUE) );
});
