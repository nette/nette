<?php

/**
 * Common code for Route test cases.
 */



function testRouteIn(Nette\Application\Route $route, $uri)
{
	T::note("==> $uri");

	$uri = new Nette\Web\UriScript("http://example.com$uri");
	$uri->scriptPath = '/';
	$uri->appendQuery(array(
		'test' => 'testvalue',
		'presenter' => 'querypresenter',
	));

	$httpRequest = new Nette\Web\HttpRequest;
	$httpRequest->initialize();
	$httpRequest->setUri($uri);

	$request = $route->match($httpRequest);

	if ($request) { // matched
		$params = $request->getParams();
		//asort($params);
		T::dump( $request->getPresenterName() );
		T::dump( $params );

		unset($params['extra']);
		$request->setParams($params);
		$result = $route->constructUrl($request, $httpRequest);
		$result = strncmp($result, 'http://example.com', 18) ? $result : substr($result, 18);
		T::dump( $result );

	} else { // not matched
		T::note('not matched');
	}
}



function testRouteOut(Nette\Application\Route $route, $link, $params = array())
{
	T::note("==> [$link]");

	$uri = new Nette\Web\UriScript("http://example.com");
	$uri->scriptPath = '/';
	$httpRequest = new Nette\Web\HttpRequest;
	$httpRequest->initialize();
	$httpRequest->setUri($uri);

	$request = new Nette\Application\PresenterRequest($link, 'GET', $params);
	$result = $route->constructUrl($request, $httpRequest);

	T::dump( $result );
}

