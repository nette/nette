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
		return 'link(' . strtr(json_encode($args), '"', "'") . ')';
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
		return 'plink(' . strtr(json_encode($args), '"', "'") . ')';
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
plink(['Homepage:'])

plink(['Homepage:'])

plink(['Homepage:action'])

plink(['Homepage:action'])

plink(['Homepage:action',10,20,'{one}&amp;two'])

plink(['Homepage:action#hash',10,20,'{one}&amp;two'])

plink(['#hash'])

plink([':',10])

plink({'0':'default','1':10,'a':20,'b':30})

link(['login'])

plink(['login',123])

link({'0':'default!','1':10,'a':20,'b':30})

<a href="link(['Homepage:'])"></a>

<a href="link({'0':'default!','1':10,'a':20,'b':30})"></a>

<a href="link(['default!#hash',10,20])"></a>
EOD

, (string) $template->setSource(<<<EOD
{plink Homepage:}

{plink  Homepage: }

{plink Homepage:action }

{plink 'Homepage:action' }

{plink Homepage:action 10, 20, '{one}&two'}

{plink Homepage:action#hash 10, 20, '{one}&two'}

{plink #hash}

{plink : 10 }

{plink default 10, 'a' => 20, 'b' => 30}

{link  \$action}

{plink \$arr['link'], \$arr['param']}

{link default! 10, 'a' => 20, 'b' => 30}

<a n:href="Homepage:"></a>

<a n:href="default! 10, 'a' => 20, 'b' => 30"></a>

<a n:href="default!#hash 10, 20"></a>
EOD
));
