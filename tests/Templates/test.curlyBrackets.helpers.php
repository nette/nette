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


Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/curly-brackets-helpers.phtml');
$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
$template->registerHelper('nl2br', 'nl2br');
$template->registerHelper('h1', array(new MyHelper, 'invoke'));
$template->registerHelper('h2', 'strtoupper');
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

$template->hello = 'Hello World';
$template->date = strtotime('2008-01-02');

$template->render();
