<?php

/**
 * Test: Nette\Latte\Engine: {include file}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Utils\Html,
	Nette\Templating\FileTemplate,
	Nette\Templating\Template;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$latte = new Latte\Engine;
$latte->compiler->defaultContentType = Latte\Compiler::CONTENT_HTML;
$template = new FileTemplate(__DIR__ . '/templates/include.latte');
$template->setCacheStorage($cache = new MockCacheStorage);
$template->registerFilter($latte);
$template->registerHelperLoader('Nette\Templating\Helpers::loader');
$template->hello = '<i>Hello</i>';

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", codefix($template->compile()));
Assert::matchFile("$path.html", $template->__toString(TRUE));
Assert::matchFile("$path.inc1.phtml", $cache->phtml['include1.latte']);
Assert::matchFile("$path.inc2.phtml", $cache->phtml['include2.latte']);
Assert::matchFile("$path.inc3.phtml", $cache->phtml['include3.latte']);


$template = new Template;
$template->registerFilter($latte);
$template->setSource('{include somefile.latte}');
Assert::exception(function() use ($template) {
	$template->render();
}, 'Nette\NotSupportedException', 'Macro {include "filename"} is supported only with Nette\Templating\IFileTemplate.');
