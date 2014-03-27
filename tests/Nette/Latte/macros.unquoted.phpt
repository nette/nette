<?php

/**
 * Test: Nette\Latte\Engine: unquoted attributes.
 *
 * @author     Jakub Vrana
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new FileTemplate(__DIR__ . '/templates/unquoted.latte');
$template->registerFilter(new Latte\Engine);
$template->x = '\' & "';

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", $template->compile());
Assert::matchFile("$path.html", $template->__toString(TRUE));
