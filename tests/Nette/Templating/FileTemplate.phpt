<?php

/**
 * Test: Nette\Templating\FileTemplate
 */

use Nette\Templating\FileTemplate,
	Nette\Utils\Html,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Nette\Latte\Engine;
$template = new FileTemplate(__DIR__ . '/template.latte');
$template->registerFilter($latte);
$template->registerHelper('translate', 'strrev');
$template->registerHelper('join', 'implode');
$template->registerHelperLoader('Nette\Templating\Helpers::loader');

$template->hello = '<i>Hello</i>';
$template->el = Html::el('div')->title('1/2"');

Assert::matchFile(__DIR__ . '/expected/FileTemplate.phtml', $template->compile());
Assert::matchFile(__DIR__ . '/expected/FileTemplate.html', $template->__toString(TRUE));
