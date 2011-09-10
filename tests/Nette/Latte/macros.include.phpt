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



Html::$xhtml = FALSE;
$template = new FileTemplate(__DIR__ . '/templates/include.latte');
$template->setCacheStorage($cache = new MockCacheStorage);
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');
$template->hello = '<i>Hello</i>';

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), codefix($template->compile()));
Assert::match(file_get_contents("$path.html"), $template->__toString(TRUE));
Assert::match(file_get_contents("$path.inc1.phtml"), $cache->phtml['include1.latte']);
Assert::match(file_get_contents("$path.inc2.phtml"), $cache->phtml['include2.latte']);
Assert::match(file_get_contents("$path.inc3.phtml"), $cache->phtml['include3.latte']);
