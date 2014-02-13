<?php

/**
 * Test: Nette\Application\UI\PresenterComponent and secured signals.
 *
 * @author     Jan Skrasek
 * @package    Nette\Application\UI
 * @subpackage UnitTests
 */

use Nette\Http;
use Nette\Application;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	/**
	 * @secured
	 */
	function handleDelete()
	{
	}
	function handleEdit()
	{
		$this->redirectUrl('http://example.com');
	}
	function getCsrfToken($control, $method, $params)
	{
		return 'hash';
	}
	function renderDefault()
	{
	}
}


$container = id(new Nette\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();
$presenter = new TestPresenter;
$container->callMethod($presenter->injectPrimary);


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('action' => NULL, 'do' => 'delete'));
	$presenter->run($request);
}, 'Nette\Application\UI\BadSignalException', "Invalid security token for signal 'delete' in class TestPresenter.");


Assert::throws(function() use ($presenter) {
	$request = new Application\Request('Test', Http\Request::GET, array('action' => NULL, 'do' => 'delete', '_sec' => 'hash'));
	$presenter->run($request);
}, 'RuntimeException', "Secured signal 'delete' did not redirect. Possible csrf-token reveal by http referer header.");


$request = new Application\Request('Test', Http\Request::GET, array('action' => NULL, 'do' => 'edit', '_sec' => 'hash'));
$response = $presenter->run($request);
Assert::true($response instanceof Nette\Application\Responses\RedirectResponse);
