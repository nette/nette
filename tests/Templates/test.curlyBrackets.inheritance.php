<h1>Nette\Templates\CurlyBracketsFilter & inheritance test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/


$tmpDir = dirname(__FILE__) . '/tmp';
foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

Environment::setVariable('tempDir', $tmpDir);

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/curly-brackets-inheritance.child.phtml');
$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
$template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');

$template->people = array('John', 'Mary', 'Paul');

$template->render();
