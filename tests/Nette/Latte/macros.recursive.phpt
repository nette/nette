<?php

/**
 * Test: Nette\Latte\Engine: general HTML test.
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$template = new FileTemplate(__DIR__ . '/templates/recursive.latte');
$template->registerFilter($latte);
$template->registerHelperLoader('Nette\Latte\Runtime\Filters::loader');

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", $template->compile());
