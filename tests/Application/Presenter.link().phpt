<?php

/**
 * Test: Nette\Application\Presenter::link()
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Environment,
	Nette\Application\PresenterRequest,
	Nette\Application\SimpleRouter;



require __DIR__ . '/../bootstrap.php';



class TestControl extends Nette\Application\Control
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



class TestPresenter extends Nette\Application\Presenter
{
	/** @var TestControl */
	public $mycontrol;

	/** @persistent */
	public $var1 = 10;

	/** @persistent bool */
	public $ok = TRUE;


	protected function createTemplate()
	{
	}


	protected function startup()
	{
		parent::startup();
		$this->mycontrol = new TestControl($this, 'mycontrol');

		// Presenter & action link
		Assert::same( '/index.php?action=product&presenter=Test', $this->link('product', array('var1' => $this->var1)) );
		Assert::same( '/index.php?var1=20&action=product&presenter=Test', $this->link('product', array('var1' => $this->var1 * 2, 'ok' => TRUE)) );
		Assert::same( '/index.php?var1=1&ok=0&action=product&presenter=Test', $this->link('product', array('var1' => TRUE, 'ok' => '0')) );
		Assert::same( '/index.php?action=product&presenter=Test', $this->link('product', array('var1' => NULL, 'ok' => 'a')) );
		Assert::same( '/index.php?var1=1&ok=0&action=product&presenter=Test', $this->link('product', array('var1' => array(1), 'ok' => FALSE)) );
		Assert::same( "error: Extra parameter for 'Test:product'.", $this->link('product', 1, 2) );
		Assert::same( '/index.php?x=1&y=2&action=product&presenter=Test', $this->link('product', array('x' => 1, 'y' => 2)) );
		Assert::same( '/index.php?action=product&presenter=Test', $this->link('product') );
		Assert::same( 'error: Destination must be non-empty string.', $this->link('') );
		Assert::same( '/index.php?x=1&y=2&action=product&presenter=Test', $this->link('product?x=1&y=2') );
		Assert::same( '/index.php?x=1&y=2&action=product&presenter=Test#fragment', $this->link('product?x=1&y=2#fragment') );
		Assert::same( 'http://localhost/index.php?x=1&y=2&action=product&presenter=Test#fragment', $this->link('//product?x=1&y=2#fragment') );

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
		Assert::same( "error: Extra parameter for signal 'TestControl:handleclick'.", $this->mycontrol->link('click', 1, 2, 3) );
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
}

class Submodule_OtherPresenter extends TestPresenter
{
}


Environment::setVariable('appDir', __DIR__);

$httpRequest = Environment::getHttpRequest();
$uri = clone $httpRequest->getUri();
$uri->scriptPath = '/index.php';
$uri->host = 'localhost';
$httpRequest->setUri($uri);

$application = Environment::getApplication();
$application->setRouter(new SimpleRouter());

$request = new PresenterRequest('Test', Nette\Web\HttpRequest::GET, array());

TestPresenter::$invalidLinkMode = TestPresenter::INVALID_LINK_WARNING;
$presenter = new TestPresenter;
$presenter->autoCanonicalize = FALSE;
$presenter->run($request);
