<?php

/**
 * Test: Nette\Latte\Engine: comments HTML test.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$template = new FileTemplate(__DIR__ . '/templates/comments.latte');
$template->registerFilter(new Latte\Engine);
$template->gt = '>';
$template->dash = '-';
$template->basePath = '/www';

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.html", $template->__toString(TRUE));
