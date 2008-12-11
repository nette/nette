<h1>Nette\Templates\CurlyBracketsFilter & link test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

class MockControl
{
	function link($destination, $args = array())
	{
		array_unshift($args, $destination);
		return 'LINK(' . implode(', ', $args) . ')';
	}
}

class MockPresenter extends MockControl
{
	function link($destination, $args = array())
	{
		array_unshift($args, $destination);
		return 'PLINK(' . implode(', ', $args) . ')';
	}
}

$tmpDir = dirname(__FILE__) . '/tmp';
foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

Environment::setVariable('tempDir', $tmpDir);

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/curly-brackets-link.phtml');
$template->registerFilter(/*Nette\Templates\*/'CurlyBracketsFilter::invoke');
$template->registerHelper('escape', /*Nette\Templates\*/'TemplateHelpers::escapeHtml');
$template->control = new MockControl;
$template->presenter = new MockPresenter;
$template->view = 'login';
$template->render();
