<h1>Nette::Application::TemplateFilters::parts test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Environment;*/
/*use Nette::Application::Template;*/

$tmpDir = dirname(__FILE__) . '/tmp';
foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

Environment::setVariable('tempDir', $tmpDir);

$template = new Template;
$template->root = dirname(__FILE__) . '/templates';
$template->registerFilter(/*Nette::Application::*/'TemplateFilters::parts');
$template->data = array(
	array('John', 10, 20),
	array('Mary', 30, 40),
	array('Paul', 50, 60),
);
$template->render('parts.phtml#main');
