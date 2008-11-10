<h1>Nette\Application link test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\Environment;*/
/*use Nette\Debug;*/
/*use Nette\Application\PresenterRequest;*/
/*use Nette\Application\SimpleRouter;*/


class TestControl extends /*Nette\Application\*/Control
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




class TestPresenter extends /*Nette\Application\*/Presenter
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


	public function beforePrepare()
	{
		$this->mycontrol = new TestControl($this, 'mycontrol');



		echo "\n<hr><h2>Presenter & view link</h2>\n";

		$uri = $this->link('product', array('var1' => $this->var1));
		echo "1.1 $uri\n\n";

		$uri = $this->link('product', array('var1' => $this->var1 * 2, 'ok' => TRUE));
		echo "1.2 $uri\n\n";

		$uri = $this->link('product', array('var1' => TRUE, 'ok' => '0'));
		echo "1.3 $uri\n\n";

		$uri = $this->link('product', array('var1' => NULL, 'ok' => 'a'));
		echo "1.4 $uri\n\n";

		$uri = $this->link('product', array('var1' => array(1), 'ok' => FALSE));
		echo "1.5 $uri\n\n";

		$uri = $this->link('product', 1, 2);
		echo "1.6 $uri\n\n";

		$uri = $this->link('product', array('x' => 1, 'y' => 2));
		echo "1.7 $uri\n\n";

		$uri = $this->link('product');
		echo "1.8 $uri\n\n";

		$uri = $this->link('');
		echo "1.9 $uri\n\n";

		$uri = $this->link('product?x=1&y=2');
		echo "1.10 $uri\n\n";




		echo "\n<hr><h2>Presenter & signal link</h2>\n";

		$uri = $this->link('buy!', array('var1' => $this->var1));
		echo "2.1 $uri\n\n";

		$uri = $this->link('buy!', array('var1' => $this->var1 * 2));
		echo "2.2 $uri\n\n";

		$uri = $this->link('buy!', 1, 2);
		echo "2.3 $uri\n\n";

		$uri = $this->link('buy!', '1', '2');
		echo "2.4 $uri\n\n";

		$uri = $this->link('buy!', '1a', NULL);
		echo "2.5 $uri\n\n";

		$uri = $this->link('buy!', TRUE, FALSE);
		echo "2.6 $uri\n\n";

		$uri = $this->link('buy!', array(1), (object) array(1));
		echo "2.7 $uri\n\n";

		$uri = $this->link('buy!', array(1, 'y' => 2));
		echo "2.8 $uri\n\n";

		$uri = $this->link('buy!', array('x' => 1, 'y' => 2, 'var1' => $this->var1));
		echo "2.9 $uri\n\n";

		$uri = $this->link('!');
		echo "2.10 $uri\n\n";

		$uri = $this->link('this', array('var1' => $this->var1));
		echo "2.11 $uri\n\n";

		$uri = $this->link('this!', array('var1' => $this->var1));
		echo "2.12 $uri\n\n";




		echo "\n<hr><h2>Component link</h2>\n";

		$uri = $this->mycontrol->link('', 0, 1);
		echo "3.1 $uri\n\n";

		$uri = $this->mycontrol->link('click', 0, 1);
		echo "3.2 $uri\n\n";

		$uri = $this->mycontrol->link('click', '0a', '1a');
		echo "3.3 $uri\n\n";

		$uri = $this->mycontrol->link('click', array(1), (object) array(1));
		echo "3.4 $uri\n\n";

		$uri = $this->mycontrol->link('click', TRUE, FALSE);
		echo "3.5 $uri\n\n";

		$uri = $this->mycontrol->link('click', NULL, '');
		echo "3.6 $uri\n\n";

		$uri = $this->mycontrol->link('click', 1, 2, 3);
		echo "3.7 $uri\n\n";

		$uri = $this->mycontrol->link('click!', array('x' => 1, 'y' => 2, 'round' => 0));
		echo "3.8 $uri\n\n";

		$uri = $this->mycontrol->link('click', array('x' => 1, 'round' => 1));
		echo "3.9 $uri\n\n";

		$uri = $this->mycontrol->link('this', array('x' => 1, 'round' => 1));
		echo "3.10 $uri\n\n";

		$uri = $this->mycontrol->link('this?x=1&round=1');
		echo "3.11 $uri\n\n";
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


Environment::setVariable('appDir', dirname(__FILE__));

$httpRequest = Environment::getHttpRequest();
$uri = $httpRequest->getUri(FALSE);
$uri->scriptPath = 'index.php';

$application = Environment::getApplication();
$application->setRouter(new SimpleRouter());

$request = new PresenterRequest('Test', /*Nette\Web\*/HttpRequest::GET, array());

TestPresenter::$invalidLinkMode = TestPresenter::INVALID_LINK_WARNING;
$presenter = new TestPresenter($request);
$presenter->autoCanonicalize = FALSE;
$presenter->run();
