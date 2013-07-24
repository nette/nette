<?php

/**
 * Test: Nette\Application\UI\Control::isControlInvalid()
 *
 * @author     Jan Tvrdík
 * @package    Nette\Application\UI
 */

use Nette\Application\UI;


require __DIR__ . '/../bootstrap.php';


class TestControl extends UI\Control
{

}


test(function() {
	$control = new TestControl();
	$child = new TestControl();
	$control->addComponent($child, 'foo');

	Assert::false($control->isControlInvalid());
	$child->invalidateControl();
	Assert::true($control->isControlInvalid());
});


test(function() {
	$control = new TestControl();
	$child = new Nette\ComponentModel\Container();
	$grandChild = new TestControl();
	$control->addComponent($child, 'foo');
	$child->addComponent($grandChild, 'bar');

	Assert::false($control->isControlInvalid());
	$grandChild->invalidateControl();
	Assert::true($control->isControlInvalid());
});
