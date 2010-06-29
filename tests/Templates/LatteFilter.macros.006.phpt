<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\Template,
	Nette\Templates\LatteFilter,
	Nette\Application\Control;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Template.inc';



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
$template->render(file_get_contents(__DIR__ . '/templates/latte.snippet.phtml'));

echo $template->compiled;



__halt_compiler() ?>
