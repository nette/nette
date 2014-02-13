<?php

/**
 * Test: Nette\DI\Compiler: filtering arguments 
 *
 * @author     Alexandru Pitis
 * @package    Nette\DI
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$compiler = new DI\Compiler;


$args = array();
$testOneInput = array();
$testOneClassOne = new stdClass;
$testOneClassOne->value = '\Namespace\TestClass';
$testOneClassOne->attributes = array('constantArgument','%configArgument%');

$testOneClassTwo = new stdClass;
$testOneClassTwo->value = '\Namespace\AnotherClass';
$testOneClassTwo->attributes = array();

$args[] = $testOneClassOne;
$args[] = $testOneClassTwo;

$testOneOutput = array(

	new Nette\DI\Statement($testOneClassOne->value,$testOneClassOne->attributes),
	new Nette\DI\Statement($testOneClassTwo->value,$testOneClassTwo->attributes),
	
);

Assert::equal($testOneOutput,$compiler::filterArguments($args));

$args = array();
$testTwoInput = array();
$testTwoClassOne = new stdClass;
$testTwoClassOne->value = '\Namespace\SuperClass::runSomethingSpecial';
$testTwoClassOne->attributes = array('constantArgument','%configArgument%');

$testTwoClassTwo = new stdClass;
$testTwoClassTwo->value = '\Namespace\BlankClass';
$testTwoClassTwo->attributes = array();

$testTwoClassThree = new stdClass;
$testTwoClassThree->value = '\Namespace\AnotherBlankClass';
$testTwoClassThree->attributes = array();

$testTwoClassFour = new stdClass;
$testTwoClassFour->value = '\Namespace\SomeClassInstance::runSomething';
$testTwoClassFour->attributes = array('testData');

$testTwoNestedArguments = array('test' => $testTwoClassThree, 'test2' => $testTwoClassFour);

$args[] = $testTwoClassOne;
$args[] = $testTwoClassTwo;
$args[] = $testTwoNestedArguments;

$testTwoOutput = array(

	new Nette\DI\Statement($testTwoClassOne->value,$testTwoClassOne->attributes),
	new Nette\DI\Statement($testTwoClassTwo->value,$testTwoClassTwo->attributes),
	array('test' => new Nette\DI\Statement($testTwoClassThree->value,$testTwoClassThree->attributes), 'test2' => new Nette\DI\Statement($testTwoClassFour->value,$testTwoClassFour->attributes))
);


Assert::equal($testTwoOutput,$compiler::filterArguments($args));
