<?php

/**
 * Test: Nette\Latte\Engine: {link ...}, {plink ...}
 *
 * @author     David Grudl
 */

use Nette\Latte,
	Nette\Bridges\ApplicationLatte\UIMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MockControl
{

	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = array_slice(func_get_args(), 1);
		}
		array_unshift($args, $destination);
		return 'link:' . strtr(json_encode($args), '"', "'");
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
		return 'plink:' . strtr(json_encode($args), '"', "'");
	}

	public function isAjax() {
		return FALSE;
	}

}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
UIMacros::install($latte->getCompiler());

$params['_control'] = new MockControl;
$params['_presenter'] = new MockPresenter;
$params['action'] = 'login';
$params['arr'] = array('link' => 'login', 'param' => 123);

Assert::match(<<<EOD
plink:['Homepage:']

plink:['Homepage:']

plink:['Homepage:action']

plink:['Homepage:action']

plink:['Homepage:action',10,20,'{one}&amp;two']

plink:['Homepage:action#hash',10,20,'{one}&amp;two']

plink:['#hash']

plink:[':',10]

plink:{'0':'default','1':10,'a':20,'b':30}

link:['login']

<a href="plink:['login',123]"></a>

<a href="link:{'0':'default!','1':10,'a':20,'b':30}"></a>

<a href="link:['Homepage:']"></a>

<a href="link:{'0':'default!','1':10,'a':20,'b':30}"></a>

<a href="link:['default!#hash',10,20]"></a>
EOD

, $latte->renderToString(<<<EOD
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

<a href="{plink \$arr['link'], \$arr['param']}"></a>

<a href="{link default! 10, 'a' => 20, 'b' => 30}"></a>

<a n:href="Homepage:"></a>

<a n:href="default! 10, 'a' => 20, 'b' => 30"></a>

<a n:href="default!#hash 10, 20"></a>
EOD
, $params));
