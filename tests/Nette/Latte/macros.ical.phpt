<?php

/**
 * Test: Nette\Latte\Engine: iCal template
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new FileTemplate(__DIR__ . '/templates/ical.latte');
$template->registerHelper('escape', 'Nette\Latte\Runtime\Filters::escapeICal');
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Latte\Runtime\Filters::loader');
$template->netteHttpResponse = new Nette\Http\Response;

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", $template->compile());
Assert::matchFile("$path.html", $template->__toString(TRUE));
