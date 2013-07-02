<?php

/**
 * Test: Nette\Latte\Macros\UIMacros, renderSnippets and control with two templates.
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
		$this->renderA();
		$this->renderB();
	}
	public function renderA()
	{
		$template = new Nette\Templating\Template;
		$template->registerFilter(new Latte\Engine);
		$template->_presenter = $this->getPresenter();
		$template->_control = $this;
		$template->say = 'Hello';
		$template->setSource('{snippet testA}{$say}{/snippet}');
		$template->render();
	}
	public function renderB()
	{
		$template = new Nette\Templating\Template;
		$template->registerFilter(new Latte\Engine);
		$template->_presenter = $this->getPresenter();
		$template->_control = $this;
		$template->say = 'world';
		$template->setSource('{snippet testB}{$say}{/snippet}');
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
			return new InnerControl();
		});
	}
	public function render()
	{
		$template = new Nette\Templating\Template;
		$template->registerFilter(new Latte\Engine);
		$template->_control = $this;
		$template->render();
	}
}


$control = new TestPresenter;
$control->snippetMode = true;


$control->emptyPayload();
$control['multi-1']->invalidateControl();
$control->render();
Assert::equal((object) array(
	'snippets' => array(
		'snippet-multi-1-testA' => 'Hello',
		'snippet-multi-1-testB' => 'world',
   ),
), $control->payload);
