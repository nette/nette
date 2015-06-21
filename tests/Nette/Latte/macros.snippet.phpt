<?php

/**
 * Test: Nette\Latte\Engine: general snippets test.
 */

use Nette\Latte;
use Nette\Utils\Html;
use Nette\Templating\FileTemplate;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new FileTemplate(__DIR__ . '/templates/snippet.latte');
$template->registerFilter(new Latte\Engine);

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", $template->compile());
