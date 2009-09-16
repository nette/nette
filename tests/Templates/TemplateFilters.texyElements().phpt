<?php

/**
 * Test: Nette\Templates\TemplateFilters::texyElements()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/
/*use Nette\Templates\TemplateFilters;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



class MockTexy
{
	function process($text, $singleLine = FALSE)
	{
		return '<...>';
	}
}


TemplateFilters::$texy = new MockTexy;

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/texy-elements.phtml');
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'texyElements'));
$template->render();



__halt_compiler();

------EXPECT------
<...>


<...>


<...>