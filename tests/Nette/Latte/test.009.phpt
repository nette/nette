<?php

/**
 * Test: Nette\Latte\Engine delimiters.
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



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
TestHelpers::purge(TEMP_DIR);



$template = new FileTemplate;
$template->setCacheStorage(new MockCacheStorage(TEMP_DIR));
$template->setFile(__DIR__ . '/templates/delimiters.latte');
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');
$template->people = array('John', 'Mary', 'Paul');

Assert::match(file_get_contents(__DIR__ . '/test.009.expect'), $template->__toString(TRUE));
