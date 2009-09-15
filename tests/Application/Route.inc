<?php


function testRoute(/*Nette\Application\*/Route $route, $uri)
{
	section($uri);

	$uri = new /*Nette\Web\*/UriScript("http://admin.texy.info$uri");
	$uri->scriptPath = '/';
	$uri->appendQuery(array(
		'test' => 'testvalue',
		'presenter' => 'querypresenter',
	));

	$httpRequest = new /*Nette\Web\*/HttpRequest;
	$httpRequest->initialize();
	$httpRequest->setUri($uri);

	$request = $route->match($httpRequest);

	if ($request) { // matched
		$params = $request->getParams();
		//asort($params);
		dump( $request->getPresenterName() );
		dump( $params );

		unset($params['extra']);
		$request->setParams($params);
		$result = $route->constructUrl($request, $httpRequest);
		$result = strncmp($result, 'http://admin.texy.info', 22) ? $result : substr($result, 22);
		dump( $result );

	} else { // not matched
		message('not matched');
	}
}

