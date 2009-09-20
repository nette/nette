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

require dirname(__FILE__) . '/Template.inc';



class MockPresenterComponent extends /*Nette\Application\*/PresenterComponent
{
	function link($destination, $args = array())
	{
		$args = http_build_query($args);
		return "LINK($destination $args)";
	}

}



$template = new MockTemplate;
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'netteLinks'));
$template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');
$template->control = new MockPresenterComponent;
$template->render(NetteTestHelpers::getSection(__FILE__, 'template'));



__halt_compiler();

-----template-----
<a href="nette:action?id=10">link</a>

<a href="nette:">link</a>

<a href="nette:#fragment">link</a>

<a href='nette:'>link</a>

<a href='nette:#fragment'>link</a>

------EXPECT------
<a href="LINK(action?id=10 )">link</a>

<a href="LINK(this! )">link</a>

<a href="LINK(this! )#fragment">link</a>

<a href='LINK(this! )'>link</a>

<a href='LINK(this! )#fragment'>link</a>
