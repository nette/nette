<?php

/**
 * Test: Nette\Latte\Engine: iCal template
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->addFilter('escape', 'Nette\Latte\Runtime\Filters::escapeICal');
$latte->addFilter(NULL, 'Nette\Latte\Runtime\Filters::loader');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	$latte->compile(__DIR__ . '/templates/ical.latte')
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/ical.latte',
		array('netteHttpResponse' => new Nette\Http\Response)
	)
);
