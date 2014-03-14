<?php

/**
 * Test: Nette\Forms\Helpers::generateHtmlName()
 *
 * @author     David Grudl
 */

use Nette\Forms\Form,
	Nette\Forms\Helpers,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function() {
	Assert::same('name', Helpers::generateHtmlName('name'));
	Assert::same('first[name]', Helpers::generateHtmlName('first-name'));
	Assert::same('first[second][name]', Helpers::generateHtmlName('first-second-name'));
	Assert::same('_submit', Helpers::generateHtmlName('submit'));
});
