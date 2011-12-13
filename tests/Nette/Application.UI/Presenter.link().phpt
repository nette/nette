<?php

/**
 * Test: Nette\Application\UI\Presenter::link()
 *
 * @author     David Grudl
 * @package    Nette\Application\UI
 * @subpackage UnitTests
 */

use Nette\Http,
	Nette\Application,
	Nette\Environment;



require __DIR__ . '/../bootstrap.php';



class TestControl extends Application\UI\Control
{
	/** @persistent array */
	public $order;

	/** @persistent int */
	public $round = 0;


	public function handleClick($x, $y)
	{
	}


	/**
	 * Loads params
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		if (isset($params['order'])) {
			$params['order'] = explode('.', $params['order']);

			// validate
			$copy = $params['order'];
			sort($copy);
			if ($copy != range(0, self::MAX)) {
				unset($params['order']);
			}
		}

		parent::loadState($params);
	}



	/**
	 * Save params
	 * @param  array
	 * @return void
	 */
	public function saveState(array & $params)
	{
		parent::saveState($params);
		if (isset($params['order'])) {
			$params['order'] = implode('.', $params['order']);
		}
	}

}



class TestPresenter extends Application\UI\Presenter
{
	/** @var TestControl */
	public $mycontrol;

	/** @persistent */
	public $var1 = 10;

	/** @persistent @var bool */
	public $ok = TRUE;


	protected function createTemplate($class = NULL)
	{
	}


	protected function startup()
	{
		parent::startup();
		$presenter = $this;
		$this->mycontrol = Assert::triggers(function () use ($presenter) {
			return new TestControl($presenter, 'mycontrol');
		}, E_USER_WARNING, 'Attaching components to parent using Nette\ComponentModel\Component::__construct() is deprecated; use $parent->addComponent($component, $name) or component factory instead.');

		// Presenter & action link
		Assert::same( '/index.php?action=product&presenter=Test', $this->link('product', array('var1' => $this->var1)) );
		Assert::same( '/index.php?var1=20&action=product&presenter=Test', $this->link('product', array('var1' => $this->var1 * 2, 'ok' => TRUE)) );
		Assert::same( '/index.php?var1=1&ok=0&action=product&presenter=Test', $this->link('product', array('var1' => TRUE, 'ok' => '0')) );
		Assert::same( '/index.php?action=product&presenter=Test', $this->link('product', array('var1' => NULL, 'ok' => 'a')) );
		Assert::same( '/index.php?var1=1&ok=0&action=product&presenter=Test', $this->link('product', array('var1' => array(1), 'ok' => FALSE)) );
		Assert::same( "error: Unable to pass parameters to action 'Test:product', missing corresponding method.", $this->link('product', 1, 2) );
		Assert::same( '/index.php?x=1&y=2&action=product&presenter=Test', $this->link('product', array('x' => 1, 'y' => 2)) );
		Assert::same( '/index.php?action=product&presenter=Test', $this->link('product') );
		Assert::same( 'error: Destination must be non-empty string.', $this->link('') );
		Assert::same( '/index.php?x=1&y=2&action=product&presenter=Test', $this->link('product?x=1&y=2') );
		Assert::same( '/index.php?x=1&y=2&action=product&presenter=Test#fragment', $this->link('product?x=1&y=2#fragment') );
		Assert::same( 'http://localhost/index.php?x=1&y=2&action=product&presenter=Test#fragment', $this->link('//product?x=1&y=2#fragment') );

		// Other presenter & action link
		Assert::same( '/index.php?var1=10&action=product&presenter=Other', $this->link('Other:product', array('var1' => $this->var1)) );
		Assert::same( '/index.php?action=product&presenter=Other', $this->link('Other:product', array('var1' => $this->var1 * 2)) );

		// Presenter & signal link
		Assert::same( '/index.php?action=default&do=buy&presenter=Test', $this->link('buy!', array('var1' => $this->var1)) );
		Assert::same( '/index.php?var1=20&action=default&do=buy&presenter=Test', $this->link('buy!', array('var1' => $this->var1 * 2)) );
		Assert::same( '/index.php?y=2&action=default&do=buy&presenter=Test', $this->link('buy!', 1, 2) );
		Assert::same( '/index.php?y=2&action=default&do=buy&presenter=Test', $this->link('buy!', '1', '2') );
		Assert::same( '/index.php?y=0&action=default&do=buy&presenter=Test', $this->link('buy!', '1a', NULL) );
		Assert::same( '/index.php?y=0&action=default&do=buy&presenter=Test', $this->link('buy!', TRUE, FALSE) );
		Assert::same( '/index.php?action=default&do=buy&presenter=Test', $this->link('buy!', array(1), (object) array(1)) );
		Assert::same( '/index.php?y=2&action=default&do=buy&presenter=Test', $this->link('buy!', array(1, 'y' => 2)) );
		Assert::same( '/index.php?y=2&action=default&do=buy&presenter=Test', $this->link('buy!', array('x' => 1, 'y' => 2, 'var1' => $this->var1)) );
		Assert::same( 'error: Signal must be non-empty string.', $this->link('!') );
		Assert::same( '/index.php?action=default&presenter=Test', $this->link('this', array('var1' => $this->var1)) );
		Assert::same( '/index.php?action=default&presenter=Test', $this->link('this!', array('var1' => $this->var1)) );

		// Component link
		Assert::same( 'error: Signal must be non-empty string.', $this->mycontrol->link('', 0, 1) );
		Assert::same( '/index.php?mycontrol-x=0&mycontrol-y=1&action=default&do=mycontrol-click&presenter=Test', $this->mycontrol->link('click', 0, 1) );
		Assert::same( '/index.php?mycontrol-x=0a&mycontrol-y=1a&action=default&do=mycontrol-click&presenter=Test', $this->mycontrol->link('click', '0a', '1a') );
		Assert::same( '/index.php?mycontrol-x=1&action=default&do=mycontrol-click&presenter=Test', $this->mycontrol->link('click', array(1), (object) array(1)) );
		Assert::same( '/index.php?mycontrol-x=1&action=default&do=mycontrol-click&presenter=Test', $this->mycontrol->link('click', TRUE, FALSE) );
		Assert::same( '/index.php?action=default&do=mycontrol-click&presenter=Test', $this->mycontrol->link('click', NULL, '') );
		Assert::same( "error: Passed more parameters than method TestControl::handleClick() expects.", $this->mycontrol->link('click', 1, 2, 3) );
		Assert::same( '/index.php?mycontrol-x=1&mycontrol-y=2&action=default&do=mycontrol-click&presenter=Test', $this->mycontrol->link('click!', array('x' => 1, 'y' => 2, 'round' => 0)) );
		Assert::same( '/index.php?mycontrol-x=1&mycontrol-round=1&action=default&do=mycontrol-click&presenter=Test', $this->mycontrol->link('click', array('x' => 1, 'round' => 1)) );
		Assert::same( '/index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test', $this->mycontrol->link('this', array('x' => 1, 'round' => 1)) );
		Assert::same( '/index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test', $this->mycontrol->link('this?x=1&round=1') );
		Assert::same( '/index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test#frag', $this->mycontrol->link('this?x=1&round=1#frag') );

		Assert::same( 'http://localhost/index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test#frag', $this->mycontrol->link('//this?x=1&round=1#frag') );
	}


	/**
	 * @view: default
	 */
	public function handleBuy($x = 1, $y = 1)
	{
	}

}


class OtherPresenter extends TestPresenter
{
	/** @persistent */
	public $var1 = 20;
}


Environment::setVariable('appDir', __DIR__);

$url = new Http\UrlScript('http://localhost/index.php');
$url->setScriptPath('/index.php');
Environment::getContext()->httpRequest = new Http\Request($url);

$application = Environment::getApplication();
$application->router[] = new Application\Routers\SimpleRouter();

$request = new Application\Request('Test', Http\Request::GET, array());

$presenter = new TestPresenter;
$presenter->invalidLinkMode = TestPresenter::INVALID_LINK_WARNING;
$presenter->setContext(Environment::getContext());
$presenter->autoCanonicalize = FALSE;
$presenter->run($request);
