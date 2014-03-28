<?php


class MockSession extends Nette\Http\Session
{
	public function __construct()
	{}
}


class MockUser extends Nette\Security\User
{
	public function __construct()
	{}
}


class MockPresenterFactory extends Nette\Object implements Nette\Application\IPresenterFactory
{
	function getPresenterClass(& $name)
	{
		return str_replace(':', 'Module\\', $name) . 'Presenter';
	}

	function createPresenter($name)
	{}
}


class MockRouter extends Nette\Object implements Nette\Application\IRouter
{
	function match(Nette\Http\IRequest $httpRequest)
	{}

	function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl)
	{}
}


class MockHttpRequest extends Nette\Http\Request
{
	public function __construct()
	{}
}


class MockTemplateFactory extends Nette\Bridges\ApplicationLatte\TemplateFactory
{
	public function __construct()
	{}
}
