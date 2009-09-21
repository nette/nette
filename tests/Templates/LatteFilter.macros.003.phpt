<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/
/*use Nette\Templates\LatteFilter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Template.inc';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Template::setCacheStorage(new MockCacheStorage(TEMP_DIR));



class MyHelper
{
	protected $count = 0;

	public function invoke($s)
	{
		$this->count++;
		return strtolower($s) . " ($this->count times)";
	}

}



$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/latte.helpers.phtml');
$template->registerFilter(new LatteFilter);
$template->registerHelper('nl2br', 'nl2br');
$template->registerHelper('h1', array(new MyHelper, 'invoke'));
$template->registerHelper('h2', 'strtoupper');
$template->registerHelper('translate', 'strrev');
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

$template->hello = 'Hello World';
$template->date = strtotime('2008-01-02');

$template->render();



__halt_compiler();
