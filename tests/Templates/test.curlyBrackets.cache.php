<h1>Nette\Templates\TemplateFilters::curlyBrackets & cache test</h1>

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
$template->registerFilter(/*Nette\Templates\*/'TemplateFilters::curlyBrackets');
$template->title = 'Hello';
$template->id = 456;
$template->cache = Environment::getCache('myTemplate');
$template->render();
