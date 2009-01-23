<h1>Nette\Templates\TemplateFilters::fragments test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/fragments.phtml#main');
$template->registerFilter('Nette\Templates\TemplateFilters::fragments');
$template->data = array(
	array('John', 10, 20),
	array('Mary', 30, 40),
	array('Paul', 50, 60),
);
$template->render();
