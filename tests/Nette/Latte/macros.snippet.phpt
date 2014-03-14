<?php

/**
 * Test: Nette\Latte\Engine: general snippets test.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Utils\Html,
	Nette\Templating\FileTemplate,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$template = new FileTemplate(__DIR__ . '/templates/snippet.latte');
$template->registerFilter(new Latte\Engine);

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", codefix($template->compile()));
