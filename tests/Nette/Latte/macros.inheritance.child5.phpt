<?php

/**
 * Test: Nette\Latte\Engine: {extends ...} test V.
 */

use Nette\Latte;
use Nette\Templating\FileTemplate;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new FileTemplate(__DIR__ . '/templates/inheritance.child5.latte');
$template->registerFilter(new Latte\Engine);

$template->ext = 'inheritance.parent.latte';

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", $template->compile());
Assert::matchFile("$path.html", $template->__toString(TRUE));
