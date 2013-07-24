<?php

/**
 * Test: Nette\Latte\Engine: {cache ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


$template = new FileTemplate(__DIR__ . '/templates/cache.latte');
$template->setCacheStorage($cache = new MockCacheStorage);
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Templating\Helpers::loader');

$template->netteCacheStorage = new Nette\Caching\Storages\DevNullStorage;
$template->title = 'Hello';
$template->id = 456;

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::matchFile("$path.phtml", codefix($template->compile()));
Assert::matchFile("$path.html", $template->__toString(TRUE));
Assert::matchFile("$path.inc.phtml", $cache->phtml['include.cache.latte']);
