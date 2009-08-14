<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Application\Route;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette\Application
 * @subpackage UnitTests
 */
class NetteApplicationRouteTest extends PHPUnit_Framework_TestCase
{


	public function assertRoute(Route $route, $uri, $expectedReq, $expectedUri)
	{
		$uri = new /*Nette\Web\*/UriScript("http://admin.texy.info$uri");
		$uri->scriptPath = '/';
		$uri->appendQuery(array(
			'test' => 'testvalue',
			'presenter' => 'querypresenter',
		));

		$httpRequest = new HttpRequest;
		$httpRequest->initialize();
		$httpRequest->setUri($uri);

		$request = $route->match($httpRequest);

		if ($request) { // matched
			$params = $request->getParams();
			//asort($params); asort($expectedReq['params']);
			$this->assertTrue($request->getPresenterName() === $expectedReq['presenter'] && $params === $expectedReq['params']);

			unset($params['extra']);
			$request->setParams($params);
			$result = $route->constructUrl($request, $httpRequest);
			$result = strncmp($result, 'http://admin.texy.info', 22) ? $result : substr($result, 22);
			$this->assertEquals($expectedUri, $result);

		} else { // not matched
			$this->assertEquals($expectedReq, $request);
		}
	}



	/**
	 * Optional presenter test.
	 * @return void
	 */
	public function testOptionalPresenter()
	{
		$route = new Route('<presenter>/<action>/<id \d{1,3}>', array(
			'action' => 'default',
			'id' => NULL,
		));

		$this->assertRoute($route,
			'/presenter/action/12/any', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);

		$this->assertRoute($route,
			'/presenter/action/12/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'action',
					'id' => '12',
					'test' => 'testvalue',
				),
			),
			'/presenter/action/12?test=testvalue'
		);

		$this->assertRoute($route,
			'/presenter/action/12', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'action',
					'id' => '12',
					'test' => 'testvalue',
				),
			),
			'/presenter/action/12?test=testvalue'
		);

		$this->assertRoute($route,
			'/presenter/action/1234', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);

		$this->assertRoute($route,
			'/presenter/action/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'action',
					'id' => NULL,
					'test' => 'testvalue',
				),
			),
			'/presenter/action/?test=testvalue'
		);

		$this->assertRoute($route,
			'/presenter/action', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'action',
					'id' => NULL,
					'test' => 'testvalue',
				),
			),
			'/presenter/action/?test=testvalue'
		);

		$this->assertRoute($route,
			'/presenter/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'default',
					'id' => NULL,
					'test' => 'testvalue',
				),
			),
			'/presenter/?test=testvalue'
		);

		$this->assertRoute($route,
			'/presenter', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'default',
					'id' => NULL,
					'test' => 'testvalue',
				),
			),
			'/presenter/?test=testvalue'
		);

		$this->assertRoute($route,
			'/', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);
	}



	/**
	 * With user class test.
	 * @return void
	 */
	public function testWithUserClass()
	{
		Route::addStyle('#numeric');
		Route::setStyleProperty('#numeric', Route::PATTERN, '\d{1,3}');

		$route = new Route('<presenter>/<id #numeric>', array());

		$this->assertRoute($route,
			'/presenter/12/', // ?test=testvalue&presenter=querypresenter
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

		$this->assertRoute($route,
			'/presenter/1234', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);

		$this->assertRoute($route,
			'/presenter/', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);
	}



	/**
	 * With user class and user pattern test.
	 * @return void
	 */
	public function testWithUserClassAndUserPattern()
	{
		$route = new Route('<presenter>/<id [\d.]+#numeric>', array());

		$this->assertRoute($route,
			'/presenter/12.34/', // ?test=testvalue&presenter=querypresenter
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

		$this->assertRoute($route,
			'/presenter/123x', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);

		$this->assertRoute($route,
			'/presenter/', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);
	}



	/**
	 * Extra default param test.
	 * @return void
	 */
	public function testExtraDefaultParam()
	{
		$route = new Route('<presenter>/<action>/<id \d{1,3}>/', array(
			'extra' => NULL,
		));


		$this->assertRoute($route,
			'/presenter/action/12/any', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);

		$this->assertRoute($route,
			'/presenter/action/12', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'action',
					'id' => '12',
					'extra' => NULL,
					'test' => 'testvalue',
				),
			),
			'/presenter/action/12/?test=testvalue'
		);

		$this->assertRoute($route,
			'/presenter/action/1234', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);

		$this->assertRoute($route,
			'/presenter/action/', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);

		$this->assertRoute($route,
			'/presenter', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);

		$this->assertRoute($route,
			'/', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);
	}



	/**
	 * No default params test.
	 * @return void
	 */
	public function testNoDefaultParams()
	{
		$route = new Route('<presenter>/<action>/<extra>', array(
		));


		$this->assertRoute($route,
			'/presenter/action/12', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'action',
					'extra' => '12',
					'test' => 'testvalue',
				),
			),
			NULL
		);
	}



	/**
	 * Combined URL param test.
	 * @return void
	 */
	public function testCombinedUrlParam()
	{
		$route = new Route('extra<presenter>/<action>', array(
			'presenter' => 'Default',
			'action' => 'default',
		));


		$this->assertRoute($route,
			'/presenter/action/', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);

		$this->assertRoute($route,
			'/extrapresenter/action/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'action',
					'test' => 'testvalue',
				),
			),
			'/extrapresenter/action?test=testvalue'
		);

		$this->assertRoute($route,
			'/extradefault/default/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Default',
				'params' =>
				array (
					'action' => 'default',
					'test' => 'testvalue',
				),
			),
			'/extra?test=testvalue'
		);

		$this->assertRoute($route,
			'/extra', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Default', //  or querypresenter ?
				'params' =>
				array (
					'action' => 'default',
					'test' => 'testvalue',
				),
			),
			'/extra?test=testvalue'
		);

		$this->assertRoute($route,
			'/', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);
	}



	/**
	 * With default presenter and action test.
	 * @return void
	 */
	public function testWithDefaultPresenterAndAction()
	{
		$route = new Route('<presenter>/<action>', array(
			'presenter' => 'Default',
			'action' => 'default',
		));


		$this->assertRoute($route,
			'/presenter/action/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'action',
					'test' => 'testvalue',
				),
			),
			'/presenter/action?test=testvalue'
		);

		$this->assertRoute($route,
			'/default/default/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Default',
				'params' =>
				array (
					'action' => 'default',
					'test' => 'testvalue',
				),
			),
			'/?test=testvalue'
		);

		$this->assertRoute($route,
			'/presenter', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'default',
					'test' => 'testvalue',
				),
			),
			'/presenter/?test=testvalue'
		);

		$this->assertRoute($route,
			'/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Default', // or querypresenter?
				'params' =>
				array (
					'action' => 'default',
					'test' => 'testvalue',
				),
			),
			'/?test=testvalue'
		);
	}



	/**
	 * One way test.
	 * @return void
	 */
	public function testOneWay()
	{
		$route = new Route('<presenter>/<action>', array(
			'presenter' => 'Default',
			'action' => 'default',
		), Route::ONE_WAY);


		$this->assertRoute($route,
			'/presenter/action/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Presenter',
				'params' =>
				array (
					'action' => 'action',
					'test' => 'testvalue',
				),
			),
			NULL
		);
	}



	/**
	 * With host test.
	 * @return void
	 */
	public function testWithHost()
	{

		$route = new Route('//<host>.texy.<domain>/<path>', array(
			'presenter' => 'Default',
			'action' => 'default',
		));


		$this->assertRoute($route,
			'/abc', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Default',
				'params' =>
				array (
					'host' => 'admin',
					'domain' => 'info',
					'path' => 'abc',
					'action' => 'default',
					'test' => 'testvalue',
				),
			),
			'/abc?test=testvalue'
		);

	}



	/**
	 * With absolute path test.
	 * @return void
	 */
	public function testWithAbsolutePath()
	{
		$route = new Route('/<abspath>/', array(
			'presenter' => 'Default',
			'action' => 'default',
		));

		$this->assertRoute($route,
			'/abc', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Default',
				'params' =>
				array (
					'abspath' => 'abc',
					'action' => 'default',
					'test' => 'testvalue',
				),
			),
			'/abc/?test=testvalue'
		);
	}



	/**
	 * With params in query test.
	 * @return void
	 */
	public function testWithParamsInQuery()
	{
		$route = new Route('<action> ? <presenter>', array(
			'presenter' => 'Default',
			'action' => 'default',
		));


		$this->assertRoute($route,
			'/action/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'querypresenter',
				'params' =>
				array (
					'action' => 'action',
					'test' => 'testvalue',
				),
			),
			'/action?test=testvalue&presenter=querypresenter'
		);

		$this->assertRoute($route,
			'/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'querypresenter', // or querypresenter?
				'params' =>
				array (
					'action' => 'default',
					'test' => 'testvalue',
				),
			),
			'/?test=testvalue&presenter=querypresenter'
		);
	}



	/**
	 * With named params in query test.
	 * @return void
	 */
	public function testWithNamedParamsInQuery()
	{
		$route = new Route(' ? action=<presenter> & act=<action [a-z]+>', array(
			'presenter' => 'Default',
			'action' => 'default',
		));


		$this->assertRoute($route,
			'/?act=action', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Default',
				'params' =>
				array (
					'action' => 'action',
					'test' => 'testvalue',
				),
			),
			'/?act=action&test=testvalue'
		);

		$this->assertRoute($route,
			'/?act=default', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Default',
				'params' =>
				array (
					'action' => 'default',
					'test' => 'testvalue',
				),
			),
			'/?test=testvalue'
		);
	}



	/**
	 * camelCaps vs dash test.
	 * @return void
	 */
	public function testCamelcapsVsDash()
	{
		$route = new Route('<presenter>', array(
			'presenter' => 'DefaultPresenter',
		));

		$this->assertRoute($route,
			'/abc-x-y-z', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'AbcXYZ',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/abc-x-y-z?test=testvalue'
		);


		$this->assertRoute($route,
			'/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'DefaultPresenter',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/?test=testvalue'
		);


		$this->assertRoute($route,
			'/--', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);
	}



	/**
	 * Modules test.
	 * @return void
	 */
	public function testModules()
	{
		$route = new Route('<presenter>', array(
			'module' => 'module:submodule',
		));

		$this->assertRoute($route,
			'/abc', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'module:submodule:Abc',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/abc?test=testvalue'
		);


		$this->assertRoute($route,
			'/', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);




		$route = new Route('<presenter>', array(
			'module' => 'Module:Submodule',
			'presenter' => 'Default',
		));

		$this->assertRoute($route,
			'/', // ?test=testvalue&presenter=querypresenter
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

		$this->assertRoute($route,
			'/module.submodule', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Module:Submodule:AnyDefault',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/module.submodule/?test=testvalue'
		);
	}



	/**
	 * URL encoding test.
	 * @return void
	 */
	public function testUrlEncoding()
	{
		$route = new Route('<param>', array(
			'presenter' => 'Presenter',
		));


		$this->assertRoute($route,
			'/a%3Ab', // ?test=testvalue&presenter=querypresenter
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
	}



	/**
	 * Secured test.
	 * @return void
	 */
	public function testSecured()
	{
		$route = new Route('<param>', array(
			'presenter' => 'Presenter',
		), Route::SECURED);


		$this->assertRoute($route,
			'/any', // ?test=testvalue&presenter=querypresenter
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
	}



	/**
	 * Dash in parameter test.
	 * @return void
	 */
	public function testDashInParameter()
	{
		$route = new Route('<para-meter>', array(
			'presenter' => 'Presenter',
		));


		$this->assertRoute($route,
			'/any', // ?test=testvalue&presenter=querypresenter
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
	}



	/**
	 * Foo parameter test.
	 * @return void
	 */
	public function testFooParameter()
	{
		$route = new Route('index<?.xml \.html?|\.php|>/', array(
			'presenter' => 'DefaultPresenter',
		));

		$this->assertRoute($route,
			'/index.', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);


		$this->assertRoute($route,
			'/index.xml', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'DefaultPresenter',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/index.xml/?test=testvalue'
		);


		$this->assertRoute($route,
			'/index.php', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'DefaultPresenter',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/index.xml/?test=testvalue'
		);


		$this->assertRoute($route,
			'/index.htm', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'DefaultPresenter',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/index.xml/?test=testvalue'
		);


		$this->assertRoute($route,
			'/index', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'DefaultPresenter',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/index.xml/?test=testvalue'
		);




		$route = new Route('index<?.xml>/', array(
			'presenter' => 'DefaultPresenter',
		));


		$this->assertRoute($route,
			'/index.', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);


		$this->assertRoute($route,
			'/index.xml', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'DefaultPresenter',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/index.xml/?test=testvalue'
		);


		$this->assertRoute($route,
			'/index.php', // ?test=testvalue&presenter=querypresenter
			NULL,
			NULL
		);


		$this->assertRoute($route,
			'/index', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'DefaultPresenter',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/index.xml/?test=testvalue'
		);
	}



	/**
	 * Filter table test.
	 * @return void
	 */
	public function testFilterTable()
	{
		Route::addStyle('#xlat', 'presenter');
		Route::setStyleProperty('#xlat', Route::FILTER_TABLE, array(
			'produkt' => 'Product',
			'kategorie' => 'Category',
			'zakaznik' => 'Customer',
			'kosik' => 'Basket',
		));

		$route = new Route('<presenter #xlat>', array());

		$this->assertRoute($route,
			'/kategorie/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Category',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/kategorie?test=testvalue'
		);


		$this->assertRoute($route,
			'/other/', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Other',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/other?test=testvalue'
		);


		$route = new Route(' ? action=<presenter #xlat>', array());

		$this->assertRoute($route,
			'/?action=kategorie', // ?test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Category',
				'params' =>
				array (
					'test' => 'testvalue',
				),
			),
			'/?test=testvalue&action=kategorie'
		);
	}




	/**
	 * Array params test.
	 * @return void
	 */
	public function testArrayParams()
	{
		$route = new Route(' ? arr=<arr>', array(
			'presenter' => 'Default',
			'arr' => '',
		));

		$this->assertRoute($route,
			'/?arr[1]=1&arr[2]=2', // &test=testvalue&presenter=querypresenter
			array (
				'presenter' => 'Default',
				'params' =>
				array (
					'arr' => array(1 => '1', 2 => '2'),
					'test' => 'testvalue',
				),
			),
			'/?arr%5B1%5D=1&arr%5B2%5D=2&test=testvalue'
		);
	}

}
