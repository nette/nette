<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Environment,
	Nette\Templates\FileTemplate,
	Nette\Templates\LatteFilter;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
TestHelpers::purge(TEMP_DIR);
FileTemplate::setCacheStorage(new MockCacheStorage(TEMP_DIR));



$template = new FileTemplate;
$template->setFile(__DIR__ . '/templates/cache.latte');
$template->registerFilter(new LatteFilter);
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

$template->title = 'Hello';
$template->id = 456;

Assert::match(file_get_contents(__DIR__ . '/LatteFilter.macros.002.expect'), $template->__toString(TRUE));
