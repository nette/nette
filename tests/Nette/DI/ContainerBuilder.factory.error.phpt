<?php

/**
 * Test: Nette\DI\ContainerBuilder and generated factories errors.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;


require __DIR__ . '/../bootstrap.php';


interface Bad1
{
	static function create();
}

Assert::exception(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad1')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Interface Bad1 must have just one non-static method create() or get().");


interface Bad2
{
	function createx();
}

Assert::exception(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad2')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Interface Bad2 must have just one non-static method create() or get().");


interface Bad3
{
	function other();
	function create();
}

Assert::exception(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad3')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Interface Bad3 must have just one non-static method create() or get().");


interface Bad4
{
	function create();
}

Assert::exception(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad4');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Method Bad4::create() has not @return annotation.");


interface Bad5
{
	function get($arg);
}

Assert::exception(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad5')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Method Bad5::get() must have no arguments.");


class Bad6
{
	public function __construct(Bar $bar, Baz $bar)
	{
	}
}

interface Bad7
{
	public function create();
}

Assert::exception(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad7')->setFactory('Bad6');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', PHP_VERSION_ID >= 50306 ? "The constuctor of 'Bad6' has two parameters with the name 'bar'." : "The constuctor of 'Bad6' has two parameters with the same name.");


class Bad8
{
	public function __construct(Bar $bar)
	{
	}
}

interface Bad9
{
	public function create(Baz $bar);
}

Assert::exception(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad9')->setFactory('Bad8');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Argument '\$bar in Bad8::__construct()' type hint doesn't match 'Baz' type hint.");

Assert::exception(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad9')->setFactory('Undefined');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Invalid factory in service 'one' definition.");
