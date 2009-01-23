<h1>Nette\Templates\CurlyBracketsFilter & snippets test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/


class MockControl implements /*Nette\Application\*/IPartiallyRenderable
{

	function invalidateControl($snippet = NULL)
	{
	}

	function isControlInvalid($snippet = NULL)
	{
	}

	static function isOutputAllowed()
	{
		return FALSE;
	}

	public function beginSnippet($name = 'main', $startTag = 'div')
	{
	}

	public function endSnippet($name = NULL)
	{
	}

}


function printSource($s)
{
	echo $s;
}


Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/curly-brackets-snippet.phtml');
$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
$template->registerFilter('printSource');
$template->control = new MockControl;
$template->render();
