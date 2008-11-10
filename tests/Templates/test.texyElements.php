<h1>Nette\Templates\TemplateFilters::texyElements test</h1>

<?php
require_once '../../Nette/loader.php';

class MockTexy
{
	function process($text, $singleLine = FALSE)
	{
		return '<...>';
	}
}

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/texy-elements.phtml');
$template->registerFilter(array(/*Nette\Templates\*/'TemplateFilters', 'texyElements'));
$template->render();
