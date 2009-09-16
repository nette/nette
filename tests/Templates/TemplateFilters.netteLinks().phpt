<?php

/**
 * Test: Nette\Templates\TemplateFilters::netteLinks()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



class MockPresenterComponent extends /*Nette\Application\*/PresenterComponent
{
	function link($destination, $args = array())
	{
		$args = http_build_query($args);
		return "LINK($destination $args)";
	}

}



$template = new Template;
//$template->setCacheStorage(new /*Nette\Caching\*/DummyStorage);
$template->setFile(dirname(__FILE__) . '/templates/nette-links.phtml');
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'netteLinks'));
$template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');
$template->control = new MockPresenterComponent;
$template->render();



__halt_compiler();

------EXPECT------
<a href="LINK(action?id=10 )">link</a>

<a href="LINK(this! )">link</a>

<a href="LINK(this! )#fragment">link</a>

<a href='LINK(this! )'>link</a>

<a href='LINK(this! )#fragment'>link</a>
