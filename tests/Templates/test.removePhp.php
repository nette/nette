<h1>Nette\Templates\TemplateFilters::removePhp test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

$template = new Template;
//$template->setCacheStorage(new /*Nette\Caching\*/DummyStorage);
$template->setFile(dirname(__FILE__) . '/templates/remove-php.phtml');
$template->registerFilter(array('Nette\Templates\TemplateFilters', 'removePhp'));
$template->render();
