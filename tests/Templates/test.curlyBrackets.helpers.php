<h1>Nette\Templates\CurlyBracketsFilter & helpers test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

class MyHelper
{
	protected $count = 0;

	public function invoke($s)
	{
		$this->count++;
		return strtolower($s) . " ($this->count times)";
	}

}


$tmpDir = dirname(__FILE__) . '/tmp';
foreach (glob("$tmpDir/*") as $file) unlink($file); // delete all files

Environment::setVariable('tempDir', $tmpDir);

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/curly-brackets-helpers.phtml');
$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
$template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');
$template->registerHelper('cache', 'Nette\Templates\CachingHelper::create');
$template->registerHelper('lower', 'Nette\String::lower');
$template->registerHelper('upper', 'Nette\String::upper');
$template->registerHelper('capitalize', 'Nette\String::capitalize');
$template->registerHelper('strip', 'Nette\Templates\TemplateHelpers::strip');
$template->registerHelper('nl2br', 'nl2br');
$template->registerHelper('truncate', 'Nette\String::truncate');
$template->registerHelper('h1', array(new MyHelper, 'invoke'));
$template->registerHelper('h2', 'strtoupper');
$template->hello = 'Hello World';

$template->render();
