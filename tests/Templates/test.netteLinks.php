<h1>Nette::Templates::TemplateFilters::netteLinks test</h1>

<?php
require_once '../../Nette/loader.php';

class MockPresenterComponent extends /*Nette::Application::*/PresenterComponent
{
	function link($destination, $args = array())
	{
		$args = http_build_query($args);
		return "LINK($destination $args)";
	}

	function ajaxLink($destination, $args = array())
	{
		$args = http_build_query($args);
		return "AJAXLINK($destination $args)";
	}

}

/*use Nette::Debug;*/
/*use Nette::Environment;*/
/*use Nette::Templates::Template;*/

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

Template::setCacheStorage(new /*Nette::Caching::*/DummyStorage);

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/nette-links.phtml');
$template->registerFilter(array(/*Nette::Templates::*/'TemplateFilters', 'netteLinks'));
$template->component = new MockPresenterComponent;
$template->render();
