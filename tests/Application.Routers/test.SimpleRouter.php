<h1>Nette::Application::SimpleRouter test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Application::SimpleRouter;*/
/*use Nette::Collections::Hashtable;*/


class MockHttpRequest extends /*Nette::Web::*/HttpRequest
{
	public $query = array();

	public $post = array();

	public function getQuery()
	{
		return new Hashtable($this->query);
	}

	public function getPost()
	{
		return new Hashtable($this->post);
	}
}


echo "\n<hr><h2>Basic usage</h2>\n";

$route = new SimpleRouter(array(
	'id' => 12,
	'any' => 'anyvalue',
));

$httpRequest = new MockHttpRequest;
$httpRequest->query = array (
	'presenter' => 'myPresenter',
	'view' => 'view',
	'id' => '12',
	'test' => 'testvalue',
);

$req = $route->match($httpRequest);
Debug::dump($req);

$url = $route->constructUrl($req, $httpRequest);
Debug::dump($url);



echo "\n<hr><h2>With module</h2>\n";

$route = new SimpleRouter(array(
	'module' => 'main:sub',
));

$httpRequest = new MockHttpRequest;
$httpRequest->query = array (
	'presenter' => 'myPresenter',
);

$req = $route->match($httpRequest);
Debug::dump($req);

$url = $route->constructUrl($req, $httpRequest);
Debug::dump($url);

$req = new /*Nette::Application::*/PresenterRequest(
	'othermodule:presenter',
	/*Nette::Application::*/PresenterRequest::HTTP_GET,
	array()
);
$url = $route->constructUrl($req, $httpRequest);
Debug::dump($url);


echo "\n<hr><h2>Secured</h2>\n";

$route = new SimpleRouter(array(
	'id' => 12,
	'any' => 'anyvalue',
), SimpleRouter::SECURED);

$url = $route->constructUrl($req, $httpRequest);
Debug::dump($url);
