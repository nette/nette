<?php

/**
 * Test: Nette\Latte\Engine: {include file}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Utils\Html,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



TestHelpers::purge(TEMP_DIR);


Html::$xhtml = FALSE;
$template = new FileTemplate;
$template->setCacheStorage($cache = new MockCacheStorage);
$template->setFile(__DIR__ . '/templates/include.latte');
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');
$template->hello = '<i>Hello</i>';

$result = $template->__toString(TRUE);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.html'), $result);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.phtml'), $cache->phtml['include.latte']);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.inc1.phtml'), $cache->phtml['include1.latte']);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.inc2.phtml'), $cache->phtml['include2.latte']);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.inc3.phtml'), $cache->phtml['include3.latte']);
