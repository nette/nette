<?php

/**
 * Test: Nette\Forms HTTP data.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_GET = $_POST = $_FILES = array();

$form = new Form();
$form->addSubmit('send', 'Send');

Assert::true( (bool) $form->isSubmitted() );
Assert::same( array(), $form->getHttpData() );
Assert::same( array(), $form->getValues(TRUE) );


$form = new Form();
$form->setMethod($form::GET);
$form->addSubmit('send', 'Send');

Assert::false( (bool) $form->isSubmitted() );
Assert::same( array(), $form->getHttpData() );
Assert::same( array(), $form->getValues(TRUE) );
