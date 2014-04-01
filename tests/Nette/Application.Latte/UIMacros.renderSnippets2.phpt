<?php

/**
 * Test: UIMacros, renderSnippets and control with two templates.
 *
 * @author     Jan Skrasek
 */

use Nette\Bridges\ApplicationLatte\UIMacros,
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
		$latte = new Latte\Engine;
		$latte->setTempDirectory(TEMP_DIR);
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$params['_presenter'] = $this->getPresenter();
		$params['_control'] = $this;
		$params['say'] = 'Hello';
		$latte->render('{snippet testA}{$say}{/snippet}', $params);
	}

	public function renderB()
	{
		$latte = new Latte\Engine;
		$latte->setTempDirectory(TEMP_DIR);
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$params['_presenter'] = $this->getPresenter();
		$params['_control'] = $this;
		$params['say'] = 'world';
		$latte->render('{snippet testB}{$say}{/snippet}', $params);
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
		$latte = new Latte\Engine;
		$latte->setTempDirectory(TEMP_DIR);
		$latte->setLoader(new Latte\Loaders\StringLoader);
		UIMacros::install($latte->getCompiler());
		$params['_control'] = $this;
		$latte->render('', $params);
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
