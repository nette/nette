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

require dirname(__FILE__) . '/Template.inc';



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



$template = new MockTemplate;
$template->registerFilter(new LatteFilter);
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

$template->control = new MockControl;
$template->form = new MockWidget;
$template->name = 'form';

$template->render(NetteTestHelpers::getSection(__FILE__, 'template'));



__halt_compiler();

-----template-----
{widget 'name'}

{widget form}

{widget form:test}

{widget $form:test}

{widget $name:test}

{widget $name:$name}

{widget form var1}

{widget form var1, 1, 2}

{widget form var1 => 5, 1, 2}
