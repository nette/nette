<?php

/**
 * Common code for Route test cases.
 */



function testRouteIn(Nette\Application\Routers\Route $route, $uri, $expectedPresenter=NULL, $expectedParams=NULL, $expectedUri=NULL)
{
	// ==> $uri

	$uri = new Nette\Http\UrlScript("http://example.com$uri");
	$uri->appendQuery(array(
		'test' => 'testvalue',
		'presenter' => 'querypresenter',
	));

	$httpRequest = new Nette\Http\Request($uri);

	$request = $route->match($httpRequest);

	if ($request) { // matched
		$params = $request->getParams();
		//asort($params);
		Assert::same( $expectedPresenter, $request->getPresenterName() );
		Assert::same( $expectedParams, $params );

		unset($params['extra']);
		$request->setParams($params);
		$result = $route->constructUrl($request, $uri);
		$result = strncmp($result, 'http://example.com', 18) ? $result : substr($result, 18);
		Assert::same( $expectedUri, $result );

	} else { // not matched
		// not matched
		Assert::null( $expectedPresenter );
	}
}



function testRouteOut(Nette\Application\Routers\Route $route, $link, $params = array())
{
	$uri = new Nette\Http\Url('http://example.com');
	$request = new Nette\Application\Request($link, 'GET', $params);
	return $route->constructUrl($request, $uri);
}
