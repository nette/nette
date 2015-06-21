<?php

/**
 * Test: Nette\Latte\Engine: comments HTML test.
 */

use Nette\Latte;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$template = new FileTemplate(__DIR__ . '/templates/comments.latte');
$template->registerFilter(new Latte\Engine);
$template->gt = '>';
$template->dash = '-';
$template->basePath = '/www';

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.html", $template->__toString(TRUE));
