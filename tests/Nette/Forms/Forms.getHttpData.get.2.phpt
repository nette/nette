<?php

/**
 * Test: Nette\Forms HTTP data.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_GET = $_POST = $_FILES = array();
});


test(function() {
	$_GET = array('item');
	$form = new Form;
	$form->setMethod($form::GET);
	$form->addSubmit('send', 'Send');

	Assert::truthy( $form->isSubmitted() );
	Assert::same( array('item'), $form->getHttpData() );
	Assert::same( array(), $form->getValues(TRUE) );
});
