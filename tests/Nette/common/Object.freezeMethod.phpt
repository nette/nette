<?php

/**
 * Test: Nette\Object freeze method.
 *
 * @author     Filip Procházka
 * @package    Nette
 * @subpackage UnitTests
 */




require __DIR__ . '/../bootstrap.php';



class TestClass extends Nette\Object
{
	public $context;

	public function injectContext(Nette\DI\Container $context)
	{
		$this->freezeMethod();
		$this->context = $context;
	}
}

$context = new Nette\DI\Container;

$obj = new TestClass;
$obj->injectContext($context);

// when called once, everything should be fine
Assert::same( $context, $obj->context );

// throw when called twice
Assert::throws(function() use ($obj, $context) {
	$obj->injectContext($context);
}, 'Nette\MemberAccessException', 'Method TestClass->injectContext() can be called only once.');

// it can be called again on another object
$obj = new TestClass;
$obj->injectContext($context);
