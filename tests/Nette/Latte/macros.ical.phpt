<?php

/**
 * Test: Latte\Engine: iCal template
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->addFilter('escape', 'Latte\Runtime\Filters::escapeICal');

Assert::matchFile(
	__DIR__ . '/expected/macros.ical.phtml',
	$latte->compile(__DIR__ . '/templates/ical.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/macros.ical.html',
	$latte->renderToString(
		__DIR__ . '/templates/ical.latte',
		array('netteHttpResponse' => new Nette\Http\Response)
	)
);
