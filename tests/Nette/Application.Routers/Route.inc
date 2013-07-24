<?php

/**
 * Common code for Route test cases.
 */


function testRouteIn(Nette\Application\Routers\Route $route, $url, $expectedPresenter=NULL, $expectedParams=NULL, $expectedUrl=NULL)
{
	// ==> $url

	$url = new Nette\Http\UrlScript("http://example.com$url");
	$url->appendQuery(array(
		'test' => 'testvalue',
		'presenter' => 'querypresenter',
	));

	$httpRequest = new Nette\Http\Request($url);

	$request = $route->match($httpRequest);

	if ($request) { // matched
		$params = $request->getParameters();
		//asort($params);
		Assert::same( $expectedPresenter, $request->getPresenterName() );
		Assert::same( $expectedParams, $params );

		unset($params['extra']);
		$request->setParameters($params);
		$result = $route->constructUrl($request, $url);
		$result = strncmp($result, 'http://example.com', 18) ? $result : substr($result, 18);
		Assert::same( $expectedUrl, $result );

	} else { // not matched
		// not matched
		Assert::null( $expectedPresenter );
	}
}


function testRouteOut(Nette\Application\Routers\Route $route, $presenter, $params = array())
{
	$url = new Nette\Http\Url('http://example.com');
	$request = new Nette\Application\Request($presenter, 'GET', $params);
	return $route->constructUrl($request, $url);
}
