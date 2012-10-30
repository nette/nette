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

Assert::throws(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad1')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Interface Bad1 must have just one non-static method create().");



interface Bad2
{
	function createx();
}

Assert::throws(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad2')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Interface Bad2 must have just one non-static method create().");



interface Bad3
{
	function other();
	function create();
}

Assert::throws(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad3')->setFactory('stdClass');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Interface Bad3 must have just one non-static method create().");



interface Bad4
{
	function create();
}

Assert::throws(function() {
	$builder = new DI\ContainerBuilder;
	$builder->addDefinition('one')->setImplement('Bad4');
	$builder->generateClasses();
}, 'Nette\InvalidStateException', "Method Bad4::create() has not @return annotation.");
