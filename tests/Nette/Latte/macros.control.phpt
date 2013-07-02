<?php

/**
 * Test: Nette\Latte\Engine: {control ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Object;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';


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


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Templating\Helpers::loader');

$template->_control = new MockComponent;
$template->form = new MockControl;
$template->name = 'form';

(string) $template->setSource('
{control \'name\'}

{control form}

{control form:test}

{control $form:test}

{control $name:test}

{control $name:$name}

{control form var1}

{control form var1, 1, 2}

{control form var1 => 5, 1, 2}
');

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
