<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Object,
	Nette\Templates\Template,
	Nette\Templates\LatteFilter;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Template.inc';



class MockControl extends Object
{
	function getWidget($name)
	{
		echo __METHOD__;
		T::dump( func_get_args() );
		return new MockWidget;
	}

}



class MockWidget extends Object
{

	function __call($name, $args)
	{
		echo __METHOD__;
		T::dump( func_get_args() );
	}

}



$template = new MockTemplate;
$template->registerFilter(new LatteFilter);
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

$template->control = new MockControl;
$template->form = new MockWidget;
$template->name = 'form';

$template->render(T::getSection(__FILE__, 'template'));



__halt_compiler() ?>

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
