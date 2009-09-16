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
/*use Nette\Application\Control;*/
/*use Nette\Templates\Template;*/
/*use Nette\Templates\LatteFilter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



class MockControl extends Control
{

	public function getSnippetId($name = NULL)
	{
		return 'sni__' . $name;
	}

}


function printSource($s)
{
	echo $s;
}



$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/latte.snippet.phtml');
$template->registerFilter(new LatteFilter);
$template->registerFilter('printSource');

$template->control = new MockControl;

$template->render();



__halt_compiler();
