<h1>Nette\Templates\CurlyBracketsFilter & cache test</h1>

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
$template->setFile(dirname(__FILE__) . '/templates/curly-brackets-cache.phtml');
$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
$template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');
$template->registerHelper('cache', 'Nette\Templates\CachingHelper::create');
$template->registerHelper('lower', 'Nette\String::lower');
$template->registerHelper('upper', 'Nette\String::upper');
$template->title = 'Hello';
$template->id = 456;
$template->render();
