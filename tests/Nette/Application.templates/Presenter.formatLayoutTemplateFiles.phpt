<?php

/**
 * Test: Presenter::formatLayoutTemplateFiles.
 */

use Nette\Application\UI\Presenter,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/one/Presenter1.inc';
require __DIR__ . '/two/Presenter2.inc';


test(function() { // with subdir templates
	$presenter = new Presenter1;
	$presenter->setParent(NULL, 'One');
	$presenter->setLayout('my');

	Assert::same( array(
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/@my.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.@my.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/@my.phtml',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.@my.phtml',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/@my.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/@my.phtml',
	), $presenter->formatLayoutTemplateFiles() );
});


test(function() { // without subdir templates
	$presenter = new Presenter2;
	$presenter->setParent(NULL, 'Two');

	Assert::same( array(
		__DIR__ . '/templates/Two/@layout.latte',
		__DIR__ . '/templates/Two.@layout.latte',
		__DIR__ . '/templates/Two/@layout.phtml',
		__DIR__ . '/templates/Two.@layout.phtml',
		__DIR__ . '/templates/@layout.latte',
		__DIR__ . '/templates/@layout.phtml',
	), $presenter->formatLayoutTemplateFiles() );
});


test(function() { // with module & subdir templates
	$presenter = new Presenter1;
	$presenter->setParent(NULL, 'Module:SubModule:One');

	Assert::same( array(
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/@layout.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.@layout.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/@layout.phtml',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.@layout.phtml',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/@layout.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/@layout.phtml',
		__DIR__ . '/templates/@layout.latte',
		__DIR__ . '/templates/@layout.phtml',
		dirname(__DIR__) . '/templates/@layout.latte',
		dirname(__DIR__) . '/templates/@layout.phtml',
	), $presenter->formatLayoutTemplateFiles() );
});


test(function() { // with module & without subdir templates
	$presenter = new Presenter2;
	$presenter->setParent(NULL, 'Module:SubModule:Two');

	Assert::same( array(
		__DIR__ . '/templates/Two/@layout.latte',
		__DIR__ . '/templates/Two.@layout.latte',
		__DIR__ . '/templates/Two/@layout.phtml',
		__DIR__ . '/templates/Two.@layout.phtml',
		__DIR__ . '/templates/@layout.latte',
		__DIR__ . '/templates/@layout.phtml',
		dirname(__DIR__) . '/templates/@layout.latte',
		dirname(__DIR__) . '/templates/@layout.phtml',
		dirname(dirname(__DIR__)) . '/templates/@layout.latte',
		dirname(dirname(__DIR__)) . '/templates/@layout.phtml',
	), $presenter->formatLayoutTemplateFiles() );
});
