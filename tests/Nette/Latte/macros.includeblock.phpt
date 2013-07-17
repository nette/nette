<?php

/**
 * Test: Nette\Latte\Engine: {includeblock ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate,
	Nette\Utils\Html;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$template = new FileTemplate(__DIR__ . '/templates/includeblock.latte');
$template->setCacheStorage($cache = new MockCacheStorage);
$template->registerFilter(new Latte\Engine);

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", codefix($template->compile()));
Assert::matchFile("$path.html", $template->__toString(TRUE));
Assert::matchFile("$path.inc.phtml", $cache->phtml['includeblock.inc.latte']);
