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
$template->setFile(__DIR__ . '/templates/inheritance.child5.latte');
$template->registerFilter(new LatteFilter);

$template->ext = 'inheritance.parent.latte';

Assert::match(file_get_contents(__DIR__ . '/test.ext.005.expect'), $template->__toString(TRUE));
