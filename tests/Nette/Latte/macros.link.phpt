<?php

/**
 * Test: Nette\Latte\Engine: {link ...}, {plink ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


class MockControl
{

	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = array_slice(func_get_args(), 1);
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
			$args = array_slice(func_get_args(), 1);
		}
		array_unshift($args, $destination);
		return 'PLINK(' . implode(', ', $args) . ')';
	}

	public function isAjax() {
		return FALSE;
	}

}


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

$template->_control = new MockControl;
$template->_presenter = new MockPresenter;
$template->action = 'login';
$template->arr = array('link' => 'login', 'param' => 123);

Assert::match(<<<EOD
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
EOD

, (string) $template->setSource(<<<EOD
{plink Homepage:}

{plink  Homepage: }

{plink Homepage:action }

{plink 'Homepage:action' }

{plink Homepage:action 10, 20, '{one}&two'}

{plink : 10 }

{plink default 10, 'a' => 20, 'b' => 30}

{link  \$action}

{plink \$arr['link'], \$arr['param']}

{link default 10, 'a' => 20, 'b' => 30}
EOD
));
