<?php

/**
 * Test: Nette\Latte\Macros\UIMacros, renderSnippets and control with two templates.
 *
 * @author     Jan Skrasek
 */

use Nette\Latte,
	Tester\Assert;


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

	function __construct()
	{
		$this->payload = new stdClass;
	}

	function getPayload()
	{
		return $this->payload;
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


$presenter = new TestPresenter;
$presenter->snippetMode = TRUE;
$presenter['multi-1']->redrawControl();
$presenter->render();
Assert::same(array(
	'snippets' => array(
		'snippet-multi-1-testA' => 'Hello',
		'snippet-multi-1-testB' => 'world',
	),
), (array) $presenter->payload);
