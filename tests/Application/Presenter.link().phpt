<?php

/**
 * Test: Nette\Application\Presenter::link()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/
/*use Nette\Application\PresenterRequest;*/
/*use Nette\Application\SimpleRouter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



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


	protected function startup()
	{
		parent::startup();
		$this->mycontrol = new TestControl($this, 'mycontrol');



		output("==> Presenter & action link");

		$uri = $this->link('product', array('var1' => $this->var1));
		dump( "1.1 $uri" );

		$uri = $this->link('product', array('var1' => $this->var1 * 2, 'ok' => TRUE));
		dump( "1.2 $uri" );

		$uri = $this->link('product', array('var1' => TRUE, 'ok' => '0'));
		dump( "1.3 $uri" );

		$uri = $this->link('product', array('var1' => NULL, 'ok' => 'a'));
		dump( "1.4 $uri" );

		$uri = $this->link('product', array('var1' => array(1), 'ok' => FALSE));
		dump( "1.5 $uri" );

		$uri = $this->link('product', 1, 2);
		dump( "1.6 $uri" );

		$uri = $this->link('product', array('x' => 1, 'y' => 2));
		dump( "1.7 $uri" );

		$uri = $this->link('product');
		dump( "1.8 $uri" );

		$uri = $this->link('');
		dump( "1.9 $uri" );

		$uri = $this->link('product?x=1&y=2');
		dump( "1.10 $uri" );

		$uri = $this->link('product?x=1&y=2#fragment');
		dump( "1.11 $uri" );

		$uri = $this->link('//product?x=1&y=2#fragment');
		dump( "1.12 $uri" );




		output("==> Presenter & signal link");

		$uri = $this->link('buy!', array('var1' => $this->var1));
		dump( "2.1 $uri" );

		$uri = $this->link('buy!', array('var1' => $this->var1 * 2));
		dump( "2.2 $uri" );

		$uri = $this->link('buy!', 1, 2);
		dump( "2.3 $uri" );

		$uri = $this->link('buy!', '1', '2');
		dump( "2.4 $uri" );

		$uri = $this->link('buy!', '1a', NULL);
		dump( "2.5 $uri" );

		$uri = $this->link('buy!', TRUE, FALSE);
		dump( "2.6 $uri" );

		$uri = $this->link('buy!', array(1), (object) array(1));
		dump( "2.7 $uri" );

		$uri = $this->link('buy!', array(1, 'y' => 2));
		dump( "2.8 $uri" );

		$uri = $this->link('buy!', array('x' => 1, 'y' => 2, 'var1' => $this->var1));
		dump( "2.9 $uri" );

		$uri = $this->link('!');
		dump( "2.10 $uri" );

		$uri = $this->link('this', array('var1' => $this->var1));
		dump( "2.11 $uri" );

		$uri = $this->link('this!', array('var1' => $this->var1));
		dump( "2.12 $uri" );




		output("==> Component link");

		$uri = $this->mycontrol->link('', 0, 1);
		dump( "3.1 $uri" );

		$uri = $this->mycontrol->link('click', 0, 1);
		dump( "3.2 $uri" );

		$uri = $this->mycontrol->link('click', '0a', '1a');
		dump( "3.3 $uri" );

		$uri = $this->mycontrol->link('click', array(1), (object) array(1));
		dump( "3.4 $uri" );

		$uri = $this->mycontrol->link('click', TRUE, FALSE);
		dump( "3.5 $uri" );

		$uri = $this->mycontrol->link('click', NULL, '');
		dump( "3.6 $uri" );

		$uri = $this->mycontrol->link('click', 1, 2, 3);
		dump( "3.7 $uri" );

		$uri = $this->mycontrol->link('click!', array('x' => 1, 'y' => 2, 'round' => 0));
		dump( "3.8 $uri" );

		$uri = $this->mycontrol->link('click', array('x' => 1, 'round' => 1));
		dump( "3.9 $uri" );

		$uri = $this->mycontrol->link('this', array('x' => 1, 'round' => 1));
		dump( "3.10 $uri" );

		$uri = $this->mycontrol->link('this?x=1&round=1');
		dump( "3.11 $uri" );

		$uri = $this->mycontrol->link('this?x=1&round=1#frag');
		dump( "3.12 $uri" );

		$uri = $this->mycontrol->link('//this?x=1&round=1#frag');
		dump( "3.13 $uri" );
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
$uri = clone $httpRequest->getUri();
$uri->scriptPath = '/index.php';
$uri->host = 'localhost';
$httpRequest->setUri($uri);

$application = Environment::getApplication();
$application->setRouter(new SimpleRouter());

$request = new PresenterRequest('Test', /*Nette\Web\*/HttpRequest::GET, array());

TestPresenter::$invalidLinkMode = TestPresenter::INVALID_LINK_WARNING;
$presenter = new TestPresenter;
$presenter->autoCanonicalize = FALSE;
$presenter->run($request);



__halt_compiler() ?>

------EXPECT------
==> Presenter & action link

string(44) "1.1 /index.php?action=product&presenter=Test"

string(52) "1.2 /index.php?var1=20&action=product&presenter=Test"

string(56) "1.3 /index.php?var1=1&ok=0&action=product&presenter=Test"

string(44) "1.4 /index.php?action=product&presenter=Test"

string(56) "1.5 /index.php?var1=1&ok=0&action=product&presenter=Test"

string(46) "1.6 error: Extra parameter for 'Test:product'."

string(52) "1.7 /index.php?x=1&y=2&action=product&presenter=Test"

string(44) "1.8 /index.php?action=product&presenter=Test"

string(48) "1.9 error: Destination must be non-empty string."

string(53) "1.10 /index.php?x=1&y=2&action=product&presenter=Test"

string(62) "1.11 /index.php?x=1&y=2&action=product&presenter=Test#fragment"

string(78) "1.12 http://localhost/index.php?x=1&y=2&action=product&presenter=Test#fragment"

==> Presenter & signal link

string(51) "2.1 /index.php?action=default&do=buy&presenter=Test"

string(59) "2.2 /index.php?var1=20&action=default&do=buy&presenter=Test"

string(55) "2.3 /index.php?y=2&action=default&do=buy&presenter=Test"

string(55) "2.4 /index.php?y=2&action=default&do=buy&presenter=Test"

string(55) "2.5 /index.php?y=0&action=default&do=buy&presenter=Test"

string(55) "2.6 /index.php?y=0&action=default&do=buy&presenter=Test"

string(51) "2.7 /index.php?action=default&do=buy&presenter=Test"

string(55) "2.8 /index.php?y=2&action=default&do=buy&presenter=Test"

string(55) "2.9 /index.php?y=2&action=default&do=buy&presenter=Test"

string(44) "2.10 error: Signal must be non-empty string."

string(45) "2.11 /index.php?action=default&presenter=Test"

string(45) "2.12 /index.php?action=default&presenter=Test"

==> Component link

string(43) "3.1 error: Signal must be non-empty string."

string(91) "3.2 /index.php?mycontrol-x=0&mycontrol-y=1&action=default&do=mycontrol-click&presenter=Test"

string(93) "3.3 /index.php?mycontrol-x=0a&mycontrol-y=1a&action=default&do=mycontrol-click&presenter=Test"

string(77) "3.4 /index.php?mycontrol-x=1&action=default&do=mycontrol-click&presenter=Test"

string(77) "3.5 /index.php?mycontrol-x=1&action=default&do=mycontrol-click&presenter=Test"

string(63) "3.6 /index.php?action=default&do=mycontrol-click&presenter=Test"

string(64) "3.7 error: Extra parameter for signal 'TestControl:handleclick'."

string(91) "3.8 /index.php?mycontrol-x=1&mycontrol-y=2&action=default&do=mycontrol-click&presenter=Test"

string(95) "3.9 /index.php?mycontrol-x=1&mycontrol-round=1&action=default&do=mycontrol-click&presenter=Test"

string(77) "3.10 /index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test"

string(77) "3.11 /index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test"

string(82) "3.12 /index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test#frag"

string(98) "3.13 http://localhost/index.php?mycontrol-x=1&mycontrol-round=1&action=default&presenter=Test#frag"
