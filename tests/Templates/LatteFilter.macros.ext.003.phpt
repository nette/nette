<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/
/*use Nette\Templates\LatteFilter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Template.inc';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Template::setCacheStorage(new MockCacheStorage(TEMP_DIR));



$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/latte.inheritance.child3.phtml');
$template->registerFilter(new LatteFilter);

$template->render();



__halt_compiler();

------EXPECT------

	Content
