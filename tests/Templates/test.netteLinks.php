<h1>Nette\Templates\TemplateFilters::netteLinks test</h1>

<?php
require_once '../../Nette/loader.php';

class MockPresenterComponent extends /*Nette\Application\*/PresenterComponent
{
	function link($destination, $args = array())
	{
		$args = http_build_query($args);
		return "LINK($destination $args)";
	}

}

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

$template = new Template;
//$template->setCacheStorage(new /*Nette\Caching\*/DummyStorage);
$template->setFile(dirname(__FILE__) . '/templates/nette-links.phtml');
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'netteLinks'));
$template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');
$template->control = new MockPresenterComponent;
$template->render();
