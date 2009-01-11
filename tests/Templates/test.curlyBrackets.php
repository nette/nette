<h1>Nette\Templates\CurlyBracketsFilter test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

Debug::enable();

$tmpDir = dirname(__FILE__) . '/tmp';
foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

Environment::setVariable('tempDir', $tmpDir);

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/curly-brackets.phtml');
$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
$template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');
$template->registerHelper('escapeJs', 'Nette\Templates\TemplateHelpers::escapeJs');
$template->registerHelper('escapeCss', 'Nette\Templates\TemplateHelpers::escapeCss');
$template->registerHelper('cache', 'Nette\Templates\CachingHelper::create');
$template->registerHelper('lower', 'Nette\String::lower');
$template->registerHelper('upper', 'Nette\String::upper');
$template->hello = '<i>Hello</i>';
$template->id = ':item';
$template->people = array('John', 'Mary', 'Paul');
$template->menu = array('about', array('product1', 'product2'), 'contact');
$template->render();
