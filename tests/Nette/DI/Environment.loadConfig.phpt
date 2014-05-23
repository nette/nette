<?php

/**
 * Test: Nette\Environment and loadConfig.
 */

use Nette\Environment,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Environment::loadConfig('files/environment.ini', 'production');

Assert::type( 'Nette\Application\Routers\SimpleRouter', Environment::getRouter() );

Assert::same( 'hello world', Environment::getVariable('foo') );

Assert::same( 'hello world', constant('HELLO_WORLD') );

Assert::equal( Nette\Utils\ArrayHash::from(array(
	'adapter' => 'pdo_mysql',
	'params' => array(
		'host' => 'db.example.com',
	),
)), Environment::getConfig('database') );
