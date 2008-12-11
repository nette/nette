<h1>Nette\Templates\CurlyBracketsFilter & texy test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

class MockTexy
{
	function process($text, $singleLine = FALSE)
	{
		return '<pre>' . nl2br($text) . '</pre>';
	}
}

$tmpDir = dirname(__FILE__) . '/tmp';
foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

Environment::setVariable('tempDir', $tmpDir);

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/curly-brackets-texy.phtml');
$template->registerFilter(/*Nette\Templates\*/'CurlyBracketsFilter::invoke');
$template->registerHelper('escape', /*Nette\Templates\*/'TemplateHelpers::escapeHtml');
$template->registerHelper('lower', /*Nette\*/'String::lower');
$template->registerHelper('texy', array(new MockTexy, 'process'));
$template->hello = '<i>Hello</i>';
$template->people = array('John', 'Mary', 'Paul');
$template->render();
