<h1>Nette\Templates\TemplateFilters::relativeLinks test</h1>

<?php
require_once '../../Nette/loader.php';


/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/relative-links.phtml');
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'relativeLinks'));

$template->baseUri = 'http://example.com/~my/';

$template->render();
