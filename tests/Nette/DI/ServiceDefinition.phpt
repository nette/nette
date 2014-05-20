<?php

/**
 * Test: ServiceDefinition
 */

use Nette\DI\ServiceDefinition,
	Nette\DI\Statement,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function(){
	$def = new ServiceDefinition;
	$def->setClass('Class');
	Assert::same( 'Class', $def->class );
	Assert::null( $def->factory );

	$def->setArguments(array(1, 2));
	Assert::same( 'Class', $def->class );
	Assert::equal( new Statement('Class', array(1, 2)), $def->factory );
});

test(function(){
	$def = new ServiceDefinition;
	$def->setClass('Class', array());
	Assert::same( 'Class', $def->class );
	Assert::null( $def->factory );
});

test(function(){
	$def = new ServiceDefinition;
	$def->setClass('Class', array(1, 2));
	Assert::same( 'Class', $def->class );
	Assert::equal( new Statement('Class', array(1, 2)), $def->factory );
});

test(function(){
	$def = new ServiceDefinition;
	$def->setFactory('Class');
	Assert::null( $def->class );
	Assert::equal( new Statement('Class', array()), $def->factory );

	$def->setArguments(array(1, 2));
	Assert::null( $def->class );
	Assert::equal( new Statement('Class', array(1, 2)), $def->factory );
});

test(function(){
	$def = new ServiceDefinition;
	$def->setFactory('Class', array(1, 2));
	Assert::null( $def->class );
	Assert::equal( new Statement('Class', array(1, 2)), $def->factory );
});

test(function(){
	$def = new ServiceDefinition;
	$def->addSetup('Class', array(1, 2));
	Assert::equal( array(
		new Statement('Class', array(1, 2)),
	), $def->setup );
});

test(function(){
	$def = new ServiceDefinition;
	$def->addTag('tag1');
	$def->addTag('tag2', array(1, 2));
	Assert::equal( array(
		'tag1' => TRUE,
		'tag2' => array(1, 2),
	), $def->tags );
});
