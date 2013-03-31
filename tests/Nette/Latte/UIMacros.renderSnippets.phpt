<?php

/**
 * Test: Nette\Latte\Macros\UIMacros and renderSnippets.
 *
 * @author     Jan Skrasek
 * @package    Nette\Latte
 */

use Nette\Latte;



require __DIR__ . '/../bootstrap.php';



class InnerControl extends Nette\Application\UI\Control
{
	public function render()
	{
		$template = new Nette\Templating\FileTemplate;
		$template->registerFilter(new Latte\Engine);
		$template->_presenter = $this->getPresenter();
		$template->_control = $this;
		$template->say = 'Hello';
		$template->setFile(__DIR__ . '/templates/snippet-included.latte');
		$template->render();
	}
}

class TestPresenter extends Nette\Application\UI\Presenter
{
	private $payload;
	function getPayload()
	{
		return $this->payload;
	}
	function emptyPayload()
	{
		$this->payload = new stdClass;
	}
	function createComponentMulti()
	{
		return new Nette\Application\UI\Multiplier(function() {
			$control = new InnerControl();
			$control->invalidateControl();
			return $control;
		});
	}
	public function render()
	{
		$template = new Nette\Templating\FileTemplate;
		$template->registerFilter(new Latte\Engine);
		$template->_control = $this;
		$template->setFile(__DIR__ . '/templates/snippet-include.latte');
		$template->render();
	}
}


$control = new TestPresenter;
$control->snippetMode = true;



$control->emptyPayload();
$control->invalidateControl();
$control['multi-1']->invalidateControl();
$control->render();
Tester\Assert::equal((object) array(
	'snippets' => array(
		'snippet--hello' => 'Hello',
		'snippet--include' => "<p>Included file #3 (A, B)</p>\n",
		'snippet--array-1' => 'Value 1',
		'snippet--array-2' => 'Value 2',
		'snippet--array-3' => 'Value 3',
		'snippet--includeSay' => 'Hello include snippet',
		'snippet-multi-1-includeSay' => 'Hello',
	),
), $control->payload);



$control->emptyPayload();
$control->validateControl();
$control['multi-1']->validateControl();
$control->invalidateControl('hello');
$control->invalidateControl('array');
$control->render();

Tester\Assert::equal((object) array(
	'snippets' => array(
		'snippet--hello' => 'Hello',
		'snippet--array-1' => 'Value 1',
		'snippet--array-2' => 'Value 2',
		'snippet--array-3' => 'Value 3',
	),
), $control->payload);
