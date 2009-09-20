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

require dirname(__FILE__) . '/Template.inc';



class MockControl extends Control
{

	public function getSnippetId($name = NULL)
	{
		return 'sni__' . $name;
	}

}



$template = new MockTemplate;
$template->registerFilter(new LatteFilter);
$template->control = new MockControl;
$template->render(file_get_contents(dirname(__FILE__) . '/templates/latte.snippet.phtml'));

echo $template->compiled;



__halt_compiler();
