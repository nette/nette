<?php

/**
 * Test: Nette\Application\UI\Control::isControlInvalid()
 *
 * @author     Jan Tvrdík
 * @package    Nette\Application\UI
 * @subpackage UnitTests
 */

use Nette\Application\UI;



require __DIR__ . '/../bootstrap.php';



class TestControl extends UI\Control
{

}



$control = new TestControl();
$child = new TestControl();
$control->addComponent($child, 'foo');

Assert::false($control->isControlInvalid());
$child->invalidateControl();
Assert::true($control->isControlInvalid());


$control = new TestControl();
$child = new Nette\ComponentModel\Container();
$grandChild = new TestControl();
$control->addComponent($child, 'foo');
$child->addComponent($grandChild, 'bar');

Assert::false($control->isControlInvalid());
$grandChild->invalidateControl();
Assert::true($control->isControlInvalid());
