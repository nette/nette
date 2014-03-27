<?php

/**
 * Test: Nette\Latte\Macros\UIMacros and renderSnippets.
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
		$latte = new Latte\Engine;
		$latte->setTempDirectory(TEMP_DIR);
		$params['_presenter'] = $this->getPresenter();
		$params['_control'] = $this;
		$params['say'] = 'Hello';
		$latte->render(__DIR__ . '/templates/snippet-included.latte', $params);
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
		$latte = new Latte\Engine;
		$latte->setTempDirectory(TEMP_DIR);
		$params['_control'] = $this;
		$latte->render(__DIR__ . '/templates/snippet-include.latte', $params);
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

$presenter = new TestPresenter;
ob_start();
$presenter->render();
$content = ob_get_clean();
Assert::matchFile(__DIR__ .'/expected/UIMacros.renderSnippets.html', $content);
