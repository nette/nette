<?php

/**
 * Test: Presenter::formatTemplateFiles.
 */

use Nette\Application\UI\Presenter;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/one/Presenter1.inc';
require __DIR__ . '/two/Presenter2.inc';


test(function () { // with subdir templates
	$presenter = new Presenter1;
	$presenter->setParent(NULL, 'One');
	$presenter->setView('view');

	Assert::same(array(
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.phtml',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.phtml',
	), $presenter->formatTemplateFiles());
});


test(function () { // without subdir templates
	$presenter = new Presenter2;
	$presenter->setParent(NULL, 'Two');
	$presenter->setView('view');

	Assert::same(array(
		__DIR__ . '/templates/Two/view.latte',
		__DIR__ . '/templates/Two.view.latte',
		__DIR__ . '/templates/Two/view.phtml',
		__DIR__ . '/templates/Two.view.phtml',
	), $presenter->formatTemplateFiles());
});


test(function () { // with module & subdir templates
	$presenter = new Presenter1;
	$presenter->setParent(NULL, 'Module:One');
	$presenter->setView('view');

	Assert::same(array(
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.latte',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One/view.phtml',
		__DIR__ . DIRECTORY_SEPARATOR . 'one/templates/One.view.phtml',
	), $presenter->formatTemplateFiles());
});


test(function () { // with module & without subdir templates
	$presenter = new Presenter2;
	$presenter->setParent(NULL, 'Module:Two');
	$presenter->setView('view');

	Assert::same(array(
		__DIR__ . '/templates/Two/view.latte',
		__DIR__ . '/templates/Two.view.latte',
		__DIR__ . '/templates/Two/view.phtml',
		__DIR__ . '/templates/Two.view.phtml',
	), $presenter->formatTemplateFiles());
});
