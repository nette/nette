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
		$template = new Nette\Templating\Template;
		$template->registerFilter(new Latte\Engine);
		$template->_presenter = $this->getPresenter();
		$template->_control = $this;
		$template->setSource(<<<EOD
			Hello {snippet test}world{/snippet}!
EOD
		);
		$template->render();
	}
}

class MultiControl extends Nette\Application\UI\Presenter
{
	private $payload;
	function getPayload()
	{
		return $this->payload;
	}
	function createComponentMulti()
	{
		$this->payload = new stdClass;
		return new Nette\Application\UI\Multiplier(function($name) {
			$control = new InnerControl();
			$control->invalidateControl();
			return $control;
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


$control = new MultiControl;
$control['multi-1'];
$control->snippetMode = true;
$control->render();

Assert::equal((object) array(
   'snippets' => array(
      'snippet-multi-1-test' => 'world',
   ),
), $control->payload);
