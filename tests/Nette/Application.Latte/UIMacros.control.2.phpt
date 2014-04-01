<?php

/**
 * Test: {control ...}
 *
 * @author     David Grudl
 */

use Nette\Object,
	Nette\Bridges\ApplicationLatte\UIMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MockComponent extends Object
{
	function getComponent($name)
	{
		Notes::add( __METHOD__ );
		Notes::add( func_get_args() );
		return new MockControl;
	}

}


class MockControl extends Object
{

	function __call($name, $args)
	{
		Notes::add( __METHOD__ );
		Notes::add( func_get_args() );
	}

}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
UIMacros::install($latte->getCompiler());

$params['_control'] = new MockComponent;
$params['form'] = new MockControl;
$params['name'] = 'form';

$latte->renderToString('
{control \'name\'}

{control form}

{control form:test}

{control $form:test}

{control $name:test}

{control $name:$name}

{control form var1}

{control form var1, 1, 2}

{control form var1 => 5, 1, 2}
', $params);

Assert::same( array(
	"MockComponent::getComponent", array("name"),
	"MockControl::__call", array("render", array()),
	"MockComponent::getComponent", array("form"),
	"MockControl::__call", array("render", array()),
	"MockComponent::getComponent", array("form"),
	"MockControl::__call", array("renderTest", array()),
	"MockControl::__call", array("renderTest", array()),
	"MockComponent::getComponent", array("form"),
	"MockControl::__call", array("renderTest", array()),
	"MockComponent::getComponent", array("form"),
	"MockControl::__call", array("renderform", array()),
	"MockComponent::getComponent", array("form"),
	"MockControl::__call", array("render", array("var1")),
	"MockComponent::getComponent", array("form"),
	"MockControl::__call", array("render", array("var1", 1, 2)),
	"MockComponent::getComponent", array("form"),
	"MockControl::__call", array("render", array(array("var1" => 5, 0 => 1, 1 => 2))),
), Notes::fetch() );
