<?php

/**
 * Test: Nette\Latte\Engine: iCal template
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$latte = new Latte\Engine;
$latte->addFilter('escape', 'Nette\Latte\Runtime\Filters::escapeICal');
$latte->addFilterLoader('Nette\Latte\Runtime\Filters::loader');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile(
	"$path.phtml",
	codefix($latte->compile(__DIR__ . '/templates/ical.latte'))
);
Assert::matchFile(
	"$path.html",
	$latte->renderToString(
		__DIR__ . '/templates/ical.latte',
		array('netteHttpResponse' => new Nette\Http\Response)
	)
);
