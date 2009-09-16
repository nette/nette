<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Object;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/
/*use Nette\Templates\LatteFilter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);



class MockControl extends Object
{
	function getWidget($name)
	{
		echo __METHOD__;
		dump( func_get_args() );
		return new MockWidget;
	}

}



class MockWidget extends Object
{

	function __call($name, $args)
	{
		echo __METHOD__;
		dump( func_get_args() );
	}

}



$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/latte.widget.phtml');
$template->registerFilter(new LatteFilter);
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

$template->control = new MockControl;
$template->form = new MockWidget;
$template->name = 'form';

$template->render();



__halt_compiler();
