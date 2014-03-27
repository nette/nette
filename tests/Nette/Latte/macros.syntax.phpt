<?php

/**
 * Test: Nette\Latte\Engine: {syntax ...}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$template = new FileTemplate(__DIR__ . '/templates/syntax.latte');
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Latte\Runtime\Filters::loader');
$template->people = array('John', 'Mary', 'Paul');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", $template->compile());
Assert::matchFile("$path.html", $template->__toString(TRUE));
