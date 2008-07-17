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
		$uri = new /*Nette::Web::*/UriScript;
		$uri->host = 'admin.texy.info';
		$uri->scriptPath = '/';
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

	//Debug::dump($route);
	$data = $route->match($httpRequest);

	echo $data ? "matched" : "no match";
	echo "\n";

	if ($data) {
		$tmp = (array) $data->getParams();
		//asort($tmp); asort($expectedReq['params']);
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
		$ok = $expectedUri === $result;
		echo 'generated: ', ($ok ? 'OK' : '***ERROR***');
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
		'presenter' => 'Presenter',
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
		'presenter' => 'Presenter',
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
		'presenter' => 'Presenter',
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
		'presenter' => 'Presenter',
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
		'presenter' => 'Presenter',
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
		'presenter' => 'Presenter',
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




echo "\n<hr><h2>With user class</h2>\n";

Route::$styles['#numeric']['pattern'] = '\d{1,3}';

$route = new Route('<presenter>/<id #numeric>', array());

test($route,
	'/presenter/12/',
	array (
		'presenter' => 'Presenter',
		'params' =>
		array (
			'id' => '12',
			'test' => 'testvalue',
		),
	),
	'/presenter/12?test=testvalue'
);

test($route,
	'/presenter/1234',
	NULL,
	NULL
);

test($route,
	'/presenter/',
	NULL,
	NULL
);



echo "\n<hr><h2>With user class and user pattern</h2>\n";

$route = new Route('<presenter>/<id [\d.]+#numeric>', array());

test($route,
	'/presenter/12.34/',
	array (
		'presenter' => 'Presenter',
		'params' =>
		array (
			'id' => '12.34',
			'test' => 'testvalue',
		),
	),
	'/presenter/12.34?test=testvalue'
);

test($route,
	'/presenter/123x',
	NULL,
	NULL
);

test($route,
	'/presenter/',
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
		'presenter' => 'Presenter',
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
		'presenter' => 'Presenter',
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
	'presenter' => 'Default',
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
		'presenter' => 'Presenter',
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
		'presenter' => 'Default',
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
		'presenter' => 'Default', //  or querypresenter ?
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
	'presenter' => 'Default',
	'view' => 'default',
));


test($route,
	'/presenter/view/',
	array (
		'presenter' => 'Presenter',
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
		'presenter' => 'Default',
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
		'presenter' => 'Presenter',
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
		'presenter' => 'Default', // or querypresenter?
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
	'presenter' => 'Default',
	'view' => 'default',
), Route::ONE_WAY);


test($route,
	'/presenter/view/',
	array (
		'presenter' => 'Presenter',
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
	'presenter' => 'Default',
	'view' => 'default',
));


test($route,
	'/abc',
	array (
		'presenter' => 'Default',
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
	'presenter' => 'Default',
	'view' => 'default',
));

test($route,
	'/abc',
	array (
		'presenter' => 'Default',
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
	'presenter' => 'Default',
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

$route = new Route('<presenter>', array(
	'presenter' => 'DefaultPresenter',
));

test($route,
	'/abc-x-y-z',
	array (
		'presenter' => 'AbcXYZ',
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
		'presenter' => 'DefaultPresenter',
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
		'presenter' => 'module:submodule:Abc',
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
	'module' => 'Module:Submodule',
	'presenter' => 'Default',
));

test($route,
	'/',
	array (
		'presenter' => 'Module:Submodule:Default',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/?test=testvalue'
);





$route = new Route('<module>/<presenter>', array(
	'presenter' => 'AnyDefault',
));

test($route,
	'/module.submodule',
	array (
		'presenter' => 'Module:Submodule:AnyDefault',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/module.submodule/?test=testvalue'
);




echo "\n<hr><h2>URL encoding</h2>\n";

$route = new Route('<param>', array(
	'presenter' => 'Presenter',
));


test($route,
	'/a%3Ab',
	array (
		'presenter' => 'Presenter',
		'params' =>
		array (
			'param' => 'a:b',
			'test' => 'testvalue',
		),
	),
	'/a%3Ab?test=testvalue'
);
