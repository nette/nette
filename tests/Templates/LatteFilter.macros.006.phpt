<?php

/**
 * Test: Nette\Templates\LatteFilter and macros test.
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Templates\LatteFilter,
	Nette\Application\Control;



require __DIR__ . '/../bootstrap.php';

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

Assert::match(file_get_contents(__DIR__ . '/LatteFilter.macros.006.expect'), $template->compiled);
