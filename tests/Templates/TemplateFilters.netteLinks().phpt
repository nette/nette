<?php

/**
 * Test: Nette\Templates\TemplateFilters::netteLinks()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



class MockPresenterComponent extends Nette\Application\PresenterComponent
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

Assert::match(<<<EOD
<a href="LINK(action?id=10 )">link</a>

<a href="LINK(this! )">link</a>

<a href="LINK(this! )#fragment">link</a>

<a href='LINK(this! )'>link</a>

<a href='LINK(this! )#fragment'>link</a>
EOD

, $template->render(<<<EOD
<a href="nette:action?id=10">link</a>

<a href="nette:">link</a>

<a href="nette:#fragment">link</a>

<a href='nette:'>link</a>

<a href='nette:#fragment'>link</a>
EOD
));
