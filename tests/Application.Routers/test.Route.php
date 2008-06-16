<h1>Nette::Application::Route test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Application::Route;*/
/*use Nette::Collections::Hashtable ;*/


class MockHttpRequest extends /*Nette::Web::*/HttpRequest
{
	public $path;

	public $query = array();

	public $post = array();

	public function getUri()
	{
		$uri = new /*Nette::Web::*/Uri;
		$uri->host = 'admin.texy.info';
		$uri->basePath = '/';
		$uri->path = $this->path;
		return $uri;
	}

	public function getQuery()
	{
		return new Hashtable($this->query);
	}

	public function getPost()
	{
		return new Hashtable($this->post);
	}
}


function test(Route $route, $uri, $expectedReq, $expectedUri)
{
	echo "$uri: ";
	$httpRequest = new MockHttpRequest;
	$httpRequest->path = $uri;
	$httpRequest->query = array(
		'test' => 'testvalue',
		'presenter' => 'querypresenter',
	);


	$data = $route->match($httpRequest);

	echo $data ? "matched" : "no match";
	echo "\n";

	if ($data) {
		$tmp = (array) $data->getParams();
		//asort($tmp);
		//asort($expectedReq['params']);
		$ok = ($data->getPresenterName() === $expectedReq['presenter'])	&& ($tmp === $expectedReq['params']);
	} else {
		$ok = $expectedReq === $data;
	}
	echo 'parsed: ', ($ok ? 'OK' : '***ERROR***');
	echo "\n";

	if ($data) {
		$data->params['extra'] = NULL;
		//$data->setParam('extra', NULL);
		$result = $route->constructUrl($data, $httpRequest);
		echo 'generated: ', ($expectedUri === $result ? 'OK' : '***ERROR***');
		echo " <code>$result</code>\n";
	}
	echo "\n\n";
}







echo "\n<hr><h2>Optional presenter</h2>\n";

$route = new Route('<presenter>/<view>/<id \d{1,3}>', array(
	'view' => 'default',
	'id' => NULL,
));

test($route,
	'/presenter/view/12/any',
	NULL,
	NULL
);

test($route,
	'/presenter/view/12/',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'view',
			'id' => '12',
			'test' => 'testvalue',
		),
	),
	'/presenter/view/12?test=testvalue'
);

test($route,
	'/presenter/view/12',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'view',
			'id' => '12',
			'test' => 'testvalue',
		),
	),
	'/presenter/view/12?test=testvalue'
);

test($route,
	'/presenter/view/1234',
	NULL,
	NULL
);

test($route,
	'/presenter/view/',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'view',
			'id' => NULL,
			'test' => 'testvalue',
		),
	),
	'/presenter/view/?test=testvalue'
);

test($route,
	'/presenter/view',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'view',
			'id' => NULL,
			'test' => 'testvalue',
		),
	),
	'/presenter/view/?test=testvalue'
);

test($route,
	'/presenter/',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'default',
			'id' => NULL,
			'test' => 'testvalue',
		),
	),
	'/presenter/?test=testvalue'
);

test($route,
	'/presenter',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'default',
			'id' => NULL,
			'test' => 'testvalue',
		),
	),
	'/presenter/?test=testvalue'
);

test($route,
	'/',
	NULL,
	NULL
);








echo "\n<hr><h2>Extra default param</h2>\n";

$route = new Route('<presenter>/<view>/<id \d{1,3}>/', array(
	'extra' => NULL,
));


test($route,
	'/presenter/view/12/any',
	NULL,
	NULL
);

test($route,
	'/presenter/view/12',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'view',
			'id' => '12',
			'extra' => NULL,
			'test' => 'testvalue',
		),
	),
	'/presenter/view/12/?test=testvalue'
);

test($route,
	'/presenter/view/1234',
	NULL,
	NULL
);

test($route,
	'/presenter/view/',
	NULL,
	NULL
);

test($route,
	'/presenter',
	NULL,
	NULL
);

test($route,
	'/',
	NULL,
	NULL
);







echo "\n<hr><h2>No default params</h2>\n";

$route = new Route('<presenter>/<view>/<extra>', array(
));


test($route,
	'/presenter/view/12',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'view',
			'extra' => '12',
			'test' => 'testvalue',
		),
	),
	NULL
);





echo "\n<hr><h2>Combined URL param</h2>\n";

$route = new Route('extra<presenter>/<view>', array(
	'presenter' => 'default',
	'view' => 'default',
));


test($route,
	'/presenter/view/',
	NULL,
	NULL
);

test($route,
	'/extrapresenter/view/',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'view',
			'test' => 'testvalue',
		),
	),
	'/extrapresenter/view?test=testvalue'
);

test($route,
	'/extradefault/default/',
	array (
		'presenter' => 'default',
		'params' =>
		array (
			'view' => 'default',
			'test' => 'testvalue',
		),
	),
	'/extra?test=testvalue'
);

test($route,
	'/extra',
	array (
		'presenter' => 'default', //  or querypresenter ?
		'params' =>
		array (
			'view' => 'default',
			'test' => 'testvalue',
		),
	),
	'/extra?test=testvalue'
);

test($route,
	'/',
	NULL,
	NULL
);








echo "\n<hr><h2>With default presenter and view</h2>\n";

$route = new Route('<presenter>/<view>', array(
	'presenter' => 'default',
	'view' => 'default',
));


test($route,
	'/presenter/view/',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'view',
			'test' => 'testvalue',
		),
	),
	'/presenter/view?test=testvalue'
);

test($route,
	'/default/default/',
	array (
		'presenter' => 'default',
		'params' =>
		array (
			'view' => 'default',
			'test' => 'testvalue',
		),
	),
	'/?test=testvalue'
);

test($route,
	'/presenter',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'default',
			'test' => 'testvalue',
		),
	),
	'/presenter/?test=testvalue'
);

test($route,
	'/',
	array (
		'presenter' => 'default', // or querypresenter?
		'params' =>
		array (
			'view' => 'default',
			'test' => 'testvalue',
		),
	),
	'/?test=testvalue'
);







echo "\n<hr><h2>One way</h2>\n";

$route = new Route('<presenter>/<view>', array(
	'presenter' => 'default',
	'view' => 'default',
), Route::ONE_WAY);


test($route,
	'/presenter/view/',
	array (
		'presenter' => 'presenter',
		'params' =>
		array (
			'view' => 'view',
			'test' => 'testvalue',
		),
	),
	NULL
);







echo "\n<hr><h2>With host</h2>\n";

$route = new Route('//<host>.texy.<domain>/<path>', array(
	'presenter' => 'default',
	'view' => 'default',
));


test($route,
	'/abc',
	array (
		'presenter' => 'default',
		'params' =>
		array (
			'host' => 'admin',
			'domain' => 'info',
			'path' => 'abc',
			'view' => 'default',
			'test' => 'testvalue',
		),
	),
	'//admin.texy.info/abc?test=testvalue'
);






echo "\n<hr><h2>With absolute path</h2>\n";

$route = new Route('/<abspath>/', array(
	'presenter' => 'default',
	'view' => 'default',
));

test($route,
	'/abc',
	array (
		'presenter' => 'default',
		'params' =>
		array (
			'abspath' => 'abc',
			'view' => 'default',
			'test' => 'testvalue',
		),
	),
	'/abc/?test=testvalue'
);





/*

echo "\n<hr><h2>Optional params</h2>\n";

$route = new Route('<? day><part [0-9]+>', array(
));

test($route, '/day13', array(...), '/day13?presenter=');
*/







echo "\n<hr><h2>With params in query</h2>\n";


$route = new Route('<view> ? <presenter>', array(
	'presenter' => 'default',
	'view' => 'default',
));


test($route,
	'/view/',
	array (
		'presenter' => 'querypresenter',
		'params' =>
		array (
			'view' => 'view',
			'test' => 'testvalue',
		),
	),
	'/view?test=testvalue&presenter=querypresenter'
);

test($route,
	'/',
	array (
		'presenter' => 'querypresenter', // or querypresenter?
		'params' =>
		array (
			'view' => 'default',
			'test' => 'testvalue',
		),
	),
	'/?test=testvalue&presenter=querypresenter'
);







echo "\n<hr><h2>With named params in query</h2>\n";


$route = new Route('<view> ? test=<presenter>', array(
	'presenter' => 'default',
	'view' => 'default',
));


test($route,
	'/view/',
	array (
		'presenter' => 'querypresenter',
		'params' =>
		array (
			'view' => 'view',
		),
	),
	'/view?test=querypresenter'
);

test($route,
	'/',
	array (
		'presenter' => 'querypresenter',
		'params' =>
		array (
			'view' => 'default',
		),
	),
	'/?test=querypresenter'
);





echo "\n<hr><h2>camelCaps vs dash</h2>\n";

$route = new Route('<presenter #d>', array(
	'presenter' => 'defaultPresenter',
));

test($route,
	'/abc-x-y-z',
	array (
		'presenter' => 'abcXYZ',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/abc-x-y-z?test=testvalue'
);


test($route,
	'/',
	array (
		'presenter' => 'defaultPresenter',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/?test=testvalue'
);


test($route,
	'/--',
	NULL,
	NULL
);




echo "\n<hr><h2>Modules</h2>\n";

$route = new Route('<presenter>', array(
	'module' => 'module:submodule',
));

test($route,
	'/abc',
	array (
		'presenter' => 'module:submodule:abc',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/abc?test=testvalue'
);


test($route,
	'/',
	NULL,
	NULL
);




$route = new Route('<presenter>', array(
	'module' => 'module:submodule',
	'presenter' => 'defaultPresenter',
));

test($route,
	'/',
	array (
		'presenter' => 'module:submodule:defaultPresenter',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/?test=testvalue'
);





$route = new Route('<module #d>/<presenter>', array(
	'presenter' => 'defaultPresenter',
));

test($route,
	'/module.submodule',
	array (
		'presenter' => 'module:submodule:defaultPresenter',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/module.submodule/?test=testvalue'
);
