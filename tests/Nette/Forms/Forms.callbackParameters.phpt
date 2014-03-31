<?php

/**
 * Test: Nette\Forms success callback takes $form and $values parameters.
 *
 * @author     rostenkowski
 */

use Nette\Utils\ArrayHash;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['text'] = 'a';

$form = new Form();
$form->addText('text');
$form->addSubmit('submit');

$types = array();

class TestFormCallbackParameters
{
	public static $results = array();

	public static function doSomething($form, $values)
	{
		static::$results[] = $form instanceof Form;
		static::$results[] = $values instanceof ArrayHash;
	}

	public static function doSomethingWithArray($form, array $values)
	{
		static::$results[] = $form instanceof Form;
		static::$results[] = is_array($values);
	}
}

// Test the method second parameter to be an ArrayHash instance by default
$m1 = array('TestFormCallbackParameters', 'doSomething');

// Test the method second parameter to be an array by type-hint
$m2 = array('TestFormCallbackParameters', 'doSomethingWithArray');

// Test the method second parameter to be an ArrayHash instance by default again
$m3 = array('TestFormCallbackParameters', 'doSomething');

// Test the closure second parameter to be an ArrayHash instance by default
$f1 = function ($form, $values) use (& $types) {
	$types[] = $form instanceof Form;
	$types[] = $values instanceof ArrayHash;
};

// Test the closure second parameter to be an array by type-hint
$f2 = function ($form, array $values) use (& $types) {
	$types[] = $form instanceof Form;
	$types [] = is_array($values);
};

// Test the closure second parameter to be an ArrayHash instance by default again
$f3 = function ($form, $values) use (& $types) {
	$types[] = $form instanceof Form;
	$types[] = $values instanceof ArrayHash;
};

// Test the second parameter in ArrayHash form to be immutable
$f4 = function ($form, $values) use (& $types) {
	$values->text = 'b';
};
$arrayHashIsImmutable = FALSE;
$f5 = function ($form, $values) use (& $arrayHashIsImmutable) {
	$arrayHashIsImmutable = $values->text === 'a';
};

foreach (array($m1, $m2, $m3, $f1, $f2, $f3, $f4, $f5) as $f) {
	$form->onSuccess[] = $f;
}
$form->fireEvents();

Assert::same(TestFormCallbackParameters::$results, array(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE));
Assert::same($types, array(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE));
Assert::true($arrayHashIsImmutable);
