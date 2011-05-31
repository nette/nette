<?php

/**
 * Test: Nette\Latte\Engine: {extends ...} test I.
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
$template->setFile(__DIR__ . '/templates/inheritance.child1.latte');
$template->registerFilter(new Latte\Engine);

$template->people = array('John', 'Mary', 'Paul');

$result = $template->__toString(TRUE);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.html'), $result);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.child.phtml'), $cache->phtml['inheritance.child1.latte']);
Assert::match(file_get_contents(__DIR__ . '/expected/' . basename(__FILE__, '.phpt') . '.parent.phtml'), $cache->phtml['inheritance.parent.latte']);
