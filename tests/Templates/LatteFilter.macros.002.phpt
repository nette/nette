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



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/latte.cache.phtml');
$template->registerFilter(new LatteFilter);
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

$template->title = 'Hello';
$template->id = 456;

$template->render();



__halt_compiler();
