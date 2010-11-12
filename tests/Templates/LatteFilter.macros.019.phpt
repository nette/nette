<?php

/**
 * Test: Nette\Templates\LatteFilter and first/sep/last test.
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Templates\FileTemplate,
	Nette\Templates\LatteFilter;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
TestHelpers::purge(TEMP_DIR);
FileTemplate::setCacheStorage(new MockCacheStorage(TEMP_DIR));



$template = new FileTemplate;
$template->setFile(__DIR__ . '/templates/first-sep-last.latte');
$template->registerFilter(new LatteFilter);
$template->people = array('John', 'Mary', 'Paul');

Assert::match(file_get_contents(__DIR__ . '/LatteFilter.macros.019.expect'), $template->__toString(TRUE));
