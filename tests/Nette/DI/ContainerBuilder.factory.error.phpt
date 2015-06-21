<?php

/**
 * Test: Nette\DI\ContainerBuilder and generated factories errors.
 */

use Nette\DI;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setClass('X')->setFactory('Unknown');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Class Unknown used in service 'one' not found or is not instantiable.");


Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setFactory('@two');
	$builder->addDefinition('two')->setFactory('Unknown');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Class Unknown used in service 'two' not found or is not instantiable.");


Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setFactory('stdClass::foo');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Factory 'stdClass::foo' used in service 'one' is not callable.");


Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setFactory('Nette\DI\Container::foo'); // has __magic
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Factory 'Nette\\DI\\Container::foo' used in service 'one' is not callable.");


Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Unknown')->setClass('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Interface Unknown used in service 'one' not found.");


interface Bad1
{
	static function create();
}

Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad1')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Interface Bad1 used in service 'one' must have just one non-static method create() or get().");


interface Bad2
{
	function createx();
}

Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad2')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Interface Bad2 used in service 'one' must have just one non-static method create() or get().");


interface Bad3
{
	function other();
	function create();
}

Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad3')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Interface Bad3 used in service 'one' must have just one non-static method create() or get().");


interface Bad4
{
	function create();
}

Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad4');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Method Bad4::create() used in service 'one' has no @return annotation.");


interface Bad5
{
	function get($arg);
}

Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad5')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Method Bad5::get() used in service 'one' must have no arguments.");


class Bad6
{
	protected function create()
	{}
}

Assert::exception(function () {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setFactory('Bad6::create');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Factory 'Bad6::create' used in service 'one' is not callable.");
