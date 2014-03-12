<?php

/**
 * Test: Nette\PhpGenerator & variadics.
 *
 * @author     Michael Moravec
 * @phpversion 5.6
 */

use Nette\PhpGenerator\Method,
	Nette\PhpGenerator\Parameter,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


// test from

interface Variadics
{
	function foo(...$foo);
	function bar($foo, array &...$bar);
}

$method = Method::from(Variadics::class .'::foo');
Assert::true($method->isVariadic());
Assert::true($method->getParameters()['foo']->isVariadic());

$method = Method::from(Variadics::class . '::bar');
Assert::true($method->isVariadic());
Assert::false($method->getParameters()['foo']->isVariadic());
Assert::true($method->getParameters()['bar']->isVariadic());
Assert::true($method->getParameters()['bar']->isReference());
Assert::same('array', $method->getParameters()['bar']->getTypeHint());



// test generating

$methods = [];


// parameterless variadic method
$methods[] = $method = new Method;
$method->setName('variadic');
$method->setVariadic(TRUE);
$method->setBody('return 42;');


// variadic method with one parameter
$methods[] = $method = new Method;
$method->setName('variadic');
$method->addParameter('foo');
$method->setVariadic(TRUE);
$method->setBody('return 42;');


// variadic method with multiple parameters
$methods[] = $method = new Method;
$method->setName('variadic');
$method->addParameter('foo');
$method->addParameter('bar');
$method->addParameter('baz', []);
$method->setVariadic(TRUE);
$method->setBody('return 42;');


// method with one variadic param
$methods[] = $method = new Method;
$method->setName('variadic');
$method->setParameters([
	(new Parameter())->setName('foo')->setVariadic(TRUE)
]);
$method->setBody('return 42;');


// method with more params, last variadic
$methods[] = $method = new Method;
$method->setName('variadic');
$method->setParameters([
	(new Parameter())->setName('foo'),
	(new Parameter())->setName('bar')->setVariadic(TRUE)
]);
$method->setBody('return 42;');


// method with typehinted variadic param
$methods[] = $method = new Method;
$method->setName('variadic');
$method->setParameters([
	(new Parameter())->setName('foo')->setVariadic(TRUE)->setTypeHint('array')
]);
$method->setBody('return 42;');


// method with typrhinted by-value variadic param
$methods[] = $method = new Method;
$method->setName('variadic');
$method->setParameters([
	(new Parameter())->setName('foo')->setVariadic(TRUE)->setTypeHint('array')->setReference(TRUE)
]);
$method->setBody('return 42;');


Assert::matchFile(__DIR__ .'/Method.variadics.expect', implode("\n\n\n\n", array_map('strval', $methods)));
