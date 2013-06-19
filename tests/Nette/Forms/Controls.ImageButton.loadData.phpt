<?php

/**
 * Test: Nette\Forms\Controls\ImageButton.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = array(
	'image' => array(1, 2),
	'container' => array(
		'image' => array(3, 4),
	),
);


test(function() {
	$form = new Form();
	$form->addImage('image');
	$form->addContainer('container')->addImage('image');

	Assert::same( array('1', '2'), $form['image']->getValue() );
	Assert::same( array('3', '4'), $form['container']['image']->getValue() );
});
