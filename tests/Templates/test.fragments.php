<h1>Nette::Templates::TemplateFilters::fragments test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Environment;*/
/*use Nette::Templates::Template;*/

$tmpDir = dirname(__FILE__) . '/tmp';
foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

Environment::setVariable('tempDir', $tmpDir);

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/fragments.phtml#main');
$template->registerFilter(/*Nette::Templates::*/'TemplateFilters::fragments');
$template->data = array(
	array('John', 10, 20),
	array('Mary', 30, 40),
	array('Paul', 50, 60),
);
$template->render();
