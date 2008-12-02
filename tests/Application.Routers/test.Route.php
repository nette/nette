<h1>Nette\Application\Route test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\Application\Route;*/


class MockHttpRequest extends /*Nette\Web\*/HttpRequest
{

	public function setPath($path)
	{
		$this->uri = new /*Nette\Web\*/UriScript;
		$this->uri->scheme = 'http';
		$this->uri->host = 'admin.texy.info';
		$this->uri->scriptPath = '/';
		@list($this->uri->path, $this->uri->query) = explode('?', $path);
		parse_str($this->uri->query, $this->query);
	}

	public function setQuery(array $query)
	{
		$this->query += $query;
	}

	public function setPost(array $post)
	{
		$this->post = $post;
	}

}



function test(Route $route, $uri, $expectedReq, $expectedUri)
{
	echo "$uri: ";
	$httpRequest = new MockHttpRequest;
	$httpRequest->setPath($uri);
	$httpRequest->setQuery(array(
		'test' => 'testvalue',
		'presenter' => 'querypresenter',
	));

	//Debug::dump($route);
	$request = $route->match($httpRequest);

	echo $request ? "matched" : "no match";
	echo "\n";

	if ($request) {
		$params = $request->getParams();
		//asort($params); asort($expectedReq['params']);
		$ok = ($request->getPresenterName() === $expectedReq['presenter'])	&& ($params === $expectedReq['params']);
	} else {
		$ok = $expectedReq === $request;
	}
	echo 'parsed: ', ($ok ? 'OK' : '***ERROR***');
	echo "\n";

	if ($request) {
		//$request->setParam('extra', NULL);
		$request->modify('params', 'extra', NULL);
		$result = $route->constructUrl($request, $httpRequest);
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

Route::addStyle('#numeric');
Route::setStyleProperty('#numeric', Route::PATTERN, '\d{1,3}');

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
	'http://admin.texy.info/abc?test=testvalue'
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


$route = new Route(' ? test=<presenter> & test2=<view [a-z]+>', array(
	'presenter' => 'default',
	'view' => 'default',
));


test($route,
	'/?test2=view',
	array (
		'presenter' => 'querypresenter',
		'params' =>
		array (
			'view' => 'view',
		),
	),
	'/?test2=view&test=querypresenter'
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


echo "\n<hr><h2>Secured</h2>\n";

$route = new Route('<param>', array(
	'presenter' => 'Presenter',
), Route::SECURED);


test($route,
	'/any',
	array (
		'presenter' => 'Presenter',
		'params' =>
		array (
			'param' => 'any',
			'test' => 'testvalue',
		),
	),
	'https://admin.texy.info/any?test=testvalue'
);


echo "\n<hr><h2>Dash in parameter</h2>\n";

$route = new Route('<para-meter>', array(
	'presenter' => 'Presenter',
));


test($route,
	'/any',
	array (
		'presenter' => 'Presenter',
		'params' =>
		array (
			'para-meter' => 'any',
			'test' => 'testvalue',
		),
	),
	'/any?test=testvalue'
);





echo "\n<hr><h2>Foo parameter</h2>\n";

$route = new Route('index<?.xml \.html?|\.php|>/', array(
	'presenter' => 'DefaultPresenter',
));

test($route,
	'/index.',
	NULL,
	NULL
);


test($route,
	'/index.php',
	array (
		'presenter' => 'DefaultPresenter',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/index.xml/?test=testvalue'
);


test($route,
	'/index.htm',
	array (
		'presenter' => 'DefaultPresenter',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/index.xml/?test=testvalue'
);


test($route,
	'/index',
	array (
		'presenter' => 'DefaultPresenter',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/index.xml/?test=testvalue'
);






echo "\n<hr><h2>Filter table</h2>\n";

Route::addStyle('#xlat', 'presenter');
Route::setStyleProperty('#xlat', Route::FILTER_TABLE, array(
	'produkt' => 'Product',
	'kategorie' => 'Category',
	'zakaznik' => 'Customer',
	'kosik' => 'Basket',
));

$route = new Route('<presenter #xlat>', array());

test($route,
	'/kategorie/',
	array (
		'presenter' => 'Category',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/kategorie?test=testvalue'
);


test($route,
	'/other/',
	array (
		'presenter' => 'Other',
		'params' =>
		array (
			'test' => 'testvalue',
		),
	),
	'/other?test=testvalue'
);
