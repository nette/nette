<h1>Nette\Templates\CurlyBracketsFilter widget test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

class MockControl extends Object
{
	function getWidget($name)
	{
		echo __METHOD__;
		Debug::dump(func_get_args());
		return new MockWidget;
	}

}


class MockWidget extends Object
{

	function __call($name, $args)
	{
		echo __METHOD__;
		Debug::dump(func_get_args());
	}

}

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/curly-brackets-widget.phtml');
$template->registerFilter($filter = new CurlyBracketsFilter);
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

$template->control = new MockControl;
$template->form = new MockWidget;
$template->name = 'form';

$template->render();
