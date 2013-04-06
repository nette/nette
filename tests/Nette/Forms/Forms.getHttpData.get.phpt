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

$_GET = $_POST = $_FILES = array();

$form = new Form();
$form->setMethod($form::GET);
$form->addSubmit('send', 'Send');

Assert::false( (bool) $form->isSubmitted() );
Assert::equal( array(), $form->getHttpData() );
Assert::equal( array(), $form->getValues(TRUE) );


$form = new Form();
$form->addSubmit('send', 'Send');

Assert::false( (bool) $form->isSubmitted() );
Assert::equal( array(), $form->getHttpData() );
Assert::equal( array(), $form->getValues(TRUE) );
