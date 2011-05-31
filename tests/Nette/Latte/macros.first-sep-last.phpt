<?php

/**
 * Test: Nette\Latte\Engine: {first}, {last}, {sep}.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



TestHelpers::purge(TEMP_DIR);



$template = new FileTemplate;
$template->setCacheStorage($cache = new MockCacheStorage);
$template->setFile(__DIR__ . '/templates/first-sep-last.latte');
$template->registerFilter(new Latte\Engine);
$template->people = array('John', 'Mary', 'Paul');

$result = $template->__toString(TRUE);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.html'), $result);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.phtml'), reset($cache->phtml));
