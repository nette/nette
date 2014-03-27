<?php

/**
 * Test: Nette\Latte\Engine: {block $name} dynamic blocks.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new FileTemplate(__DIR__ . '/templates/dynamicblocks.latte');
$template->registerFilter(new Latte\Engine);

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", $template->compile());
Assert::matchFile("$path.html", $template->__toString(TRUE));
