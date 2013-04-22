<?php

/**
 * Test: Nette\Environment and loadConfig.
 *
 * @author     David Grudl
 * @package    Nette
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



Environment::loadConfig('files/config.old.ini', 'production');

Assert::true( Environment::getRouter() instanceof Nette\Application\Routers\SimpleRouter );

Assert::same( 'hello world', Environment::getVariable('foo') );

Assert::same( 'hello world', constant('HELLO_WORLD') );

Assert::equal( Nette\ArrayHash::from(array(
	'adapter' => 'pdo_mysql',
	'params' => array(
		'host' => 'db.example.com',
	),
)), Environment::getConfig('database') );
