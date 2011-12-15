<?php

/**
 * Test: Nette\Latte\Engine: {widget ...}
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Object;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



class MockControl extends Object
{
	function getWidget($name)
	{
		TestHelpers::note( __METHOD__ );
		TestHelpers::note( func_get_args() );
		return new MockWidget;
	}

}



class MockWidget extends Object
{

	function __call($name, $args)
	{
		TestHelpers::note( __METHOD__ );
		TestHelpers::note( func_get_args() );
	}

}



$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');

$template->_control = new MockControl;
$template->form = new MockWidget;
$template->name = 'form';

(string) $template->setSource('
{widget \'name\'}

{widget form}

{widget form:test}

{widget $form:test}

{widget $name:test}

{widget $name:$name}

{widget form var1}

{widget form var1, 1, 2}

{widget form var1 => 5, 1, 2}
');

Assert::same( array(
	"MockControl::getWidget", array("name"),
	"MockWidget::__call", array("render", array()),
	"MockControl::getWidget", array("form"),
	"MockWidget::__call", array("render", array()),
	"MockControl::getWidget", array("form"),
	"MockWidget::__call", array("renderTest", array()),
	"MockWidget::__call", array("renderTest", array()),
	"MockControl::getWidget", array("form"),
	"MockWidget::__call", array("renderTest", array()),
	"MockControl::getWidget", array("form"),
	"MockWidget::__call", array("renderform", array()),
	"MockControl::getWidget", array("form"),
	"MockWidget::__call", array("render", array("var1")),
	"MockControl::getWidget", array("form"),
	"MockWidget::__call", array("render", array("var1", 1, 2)),
	"MockControl::getWidget", array("form"),
	"MockWidget::__call", array("render", array(array("var1" => 5, 0 => 1, 1 => 2))),
), TestHelpers::fetchNotes() );
