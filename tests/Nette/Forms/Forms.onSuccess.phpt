<?php

/**
 * Test: Nette\Forms onSuccess.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';

$called = array();

$form = new Form;
$form->addText('name');
$form->addSubmit('submit');
$form->onSuccess[] = function() use (& $called) {
	$called[] = 1;
};
$form->onSuccess[] = function($form) use (& $called) {
	$called[] = 2;
	$form['name']->addError('error');
};
$form->onSuccess[] = function() use (& $called) {
	$called[] = 3;
};
$form->onSuccess[] = function() use (& $called) {
	$called[] = 4;
};
$form->onError[] = function() use (& $called) {
	$called[] = 'err';
};


$form->fireEvents();

Assert::same(array(1, 2, 'err'), $called);
