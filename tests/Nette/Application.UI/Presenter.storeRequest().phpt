<?php

/**
 * Test: Nette\Application\UI\Presenter::storeRequest()
 *
 * @author     Matěj Koubík
 */

use Nette\Http,
	Nette\Application,
	Nette\DI,
	Nette\Security,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class TestPresenter extends Application\UI\Presenter
{
	public function sendTemplate($value='') {}
}

class MockSession extends Http\Session
{
	public $testSection;

	public function __construct() {}

	public function getSection($section, $class = 'Nette\Http\SessionSection')
	{
		return $this->testSection;
	}
}

class MockSessionSection extends Nette\Object implements \ArrayAccess
{
	public $testedKeyExistence;
	public $storedKey;
	public $storedValue;
	public $testExpiration;
	public $testExpirationVariables;

	public function __isset($name)
	{
		$this->testedKeyExistence = $name;
		return false;
	}

	public function __set($name, $value)
	{
		$this->storedKey = $name;
		$this->storedValue = $value;
	}

	public function setExpiration($expiraton, $variables = NULL)
	{
		$this->testExpiration = $expiraton;
		$this->testExpirationVariables = $variables;
	}

	public function offsetExists($name)
	{
		return $this->__isset($name);
	}

	public function offsetSet($name, $value)
	{
		$this->__set($name, $value);
	}

	public function offsetGet($name) {}
	public function offsetUnset($name) {}
}

class MockUser extends Security\User
{
	public function __construct() {}

	public function getId()
	{
		return 'test_id';
	}
}

class MockPresenterFactory extends Nette\Object implements Nette\Application\IPresenterFactory
{
	function getPresenterClass(& $name) {}

	function createPresenter($name) {}
}

class MockRouter extends Nette\Object implements Nette\Application\IRouter
{
	function match(Nette\Http\IRequest $httpRequest) {}

	function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl) {}
}

class MockHttpRequest extends Http\Request
{
	public function __construct() {}
}


$presenter = new TestPresenter();
$presenter->injectPrimary(
	new DI\Container,
	new MockPresenterFactory,
	new MockRouter,
	new MockHttpRequest,
	new Http\Response,
	$session = new MockSession,
	$user = new MockUser
);

$section = $session->testSection = new MockSessionSection($session);

$applicationRequest = new Application\Request('', '', array());
$presenter->run($applicationRequest);

$expiration = '+1 year';
$key = $presenter->storeRequest($expiration);

Assert::same($expiration, $section->testExpiration);
Assert::same($key, $section->testExpirationVariables);
Assert::same($key, $section->testedKeyExistence);
Assert::same($key, $section->storedKey);
Assert::same(array($user->getId(), $applicationRequest), $section->storedValue);
