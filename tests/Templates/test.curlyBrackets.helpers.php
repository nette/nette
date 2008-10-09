<h1>Nette::Templates::TemplateFilters::curlyBrackets & helpers test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Environment;*/
/*use Nette::Templates::Template;*/

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
$template->registerHelper('h1', array(new MyHelper, 'invoke'));
$template->hello = 'Hello World';

$template->registerFilter(/*Nette::Templates::*/'TemplateFilters::curlyBrackets');
TemplateFilters::$curlyHelpers['h2'] = 'strtoupper(%s)';

$template->render();
