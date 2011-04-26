<?php

/**
 * Test: Nette\Latte\Engine and first/sep/last test.
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



// purge temporary directory
TestHelpers::purge(TEMP_DIR);



$template = new FileTemplate;
$template->setCacheStorage(new MockCacheStorage(TEMP_DIR));
$template->setFile(__DIR__ . '/templates/first-sep-last.latte');
$template->registerFilter(new Latte\Engine);
$template->people = array('John', 'Mary', 'Paul');

Assert::match(file_get_contents(__DIR__ . '/test.019.expect'), $template->__toString(TRUE));
