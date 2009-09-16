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



class MockTexy
{
	function process($text, $singleLine = FALSE)
	{
		return '<pre>' . $text . '</pre>';
	}
}



$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/latte.texy.phtml');
$template->registerFilter(new LatteFilter);
$template->registerHelper('texy', array(new MockTexy, 'process'));
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

$template->hello = '<i>Hello</i>';
$template->people = array('John', 'Mary', 'Paul');

$template->render();



__halt_compiler();
