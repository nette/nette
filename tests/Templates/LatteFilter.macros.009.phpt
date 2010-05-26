<?php

/**
 * Test: Nette\Templates\LatteFilter delimiters.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\Template,
	Nette\Templates\LatteFilter;



require __DIR__ . '/../NetteTest/initialize.php';

require __DIR__ . '/Template.inc';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Template::setCacheStorage(new MockCacheStorage(TEMP_DIR));



$template = new Template;
$template->setFile(__DIR__ . '/templates/latte.delimiters.phtml');
$template->registerFilter(new LatteFilter);
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');
$template->people = array('John', 'Mary', 'Paul');

$template->render();



__halt_compiler() ?>
