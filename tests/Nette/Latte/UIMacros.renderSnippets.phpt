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
			$control = new InnerControl();
			$control->redrawControl();
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


$presenter = new TestPresenter;
$presenter->snippetMode = TRUE;
$presenter->redrawControl();
$presenter['multi-1']->redrawControl();
$presenter->render();
Assert::same(array(
	'snippets' => array(
		'snippet--hello' => 'Hello',
		'snippet--include' => "<p>Included file #3 (A, B)</p>\n",
		'snippet--array-1' => 'Value 1',
		'snippet--array-2' => 'Value 2',
		'snippet--array-3' => 'Value 3',
		'snippet--array2-1' => 'Value 1',
		'snippet--array2-2' => 'Value 2',
		'snippet--array2-3' => 'Value 3',
		'snippet--includeSay' => 'Hello include snippet',
		'snippet-multi-1-includeSay' => 'Hello',
	),
), (array) $presenter->payload);



$presenter = new TestPresenter;
$presenter->snippetMode = TRUE;
$presenter->redrawControl('hello');
$presenter->redrawControl('array');
$presenter->render();

Assert::same(array(
	'snippets' => array(
		'snippet--hello' => 'Hello',
		'snippet--array-1' => 'Value 1',
		'snippet--array-2' => 'Value 2',
		'snippet--array-3' => 'Value 3',
	),
), (array) $presenter->payload);
