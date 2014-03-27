<?php

/**
 * Test: Nette\Latte\Engine: dynamic snippets test.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Nette\Templating\FileTemplate,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new FileTemplate(__DIR__ . '/templates/dynamicsnippets.alt.latte');
$template->registerFilter(new Latte\Engine);

$result = $template->compile();
$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", $result);
