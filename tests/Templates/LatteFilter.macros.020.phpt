<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
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



$template = new FileTemplate;
$template->setCacheStorage(new MockCacheStorage(TEMP_DIR));
$template->registerFilter(new LatteFilter);

Assert::match(file_get_contents(__DIR__ . '/LatteFilter.macros.020.expect'), $template->compile(file_get_contents(__DIR__ . '/templates/isLinkCurrent.latte')));
