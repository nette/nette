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

use Nette\Templates\Template,
	Nette\Templates\LatteFilter;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Template.inc';



class MockControl
{

	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}
		array_unshift($args, $destination);
		return 'LINK(' . implode(', ', $args) . ')';
	}

}



class MockPresenter extends MockControl
{

	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}
		array_unshift($args, $destination);
		return 'PLINK(' . implode(', ', $args) . ')';
	}

}



$template = new MockTemplate;
$template->registerFilter(new LatteFilter);

$template->control = new MockControl;
$template->presenter = new MockPresenter;
$template->action = 'login';
$template->arr = array('link' => 'login', 'param' => 123);

$template->render(T::getSection(__FILE__, 'template'));



__halt_compiler() ?>

-----template-----
{plink Homepage:}

{plink  Homepage: }

{plink Homepage:action }

{plink 'Homepage:action' }

{plink Homepage:action 10, 20, '{one}&two'}

{plink : 10 }

{plink default 10, 'a' => 20, 'b' => 30}

{link  $action}

{plink $arr['link'], $arr['param']}

{link default 10, 'a' => 20, 'b' => 30}

------EXPECT------
PLINK(Homepage:)

PLINK(Homepage:)

PLINK(Homepage:action)

PLINK(Homepage:action)

PLINK(Homepage:action, 10, 20, {one}&amp;two)

PLINK(:, 10)

PLINK(default, 10, 20, 30)

LINK(login)

PLINK(login, 123)

LINK(default, 10, 20, 30)
