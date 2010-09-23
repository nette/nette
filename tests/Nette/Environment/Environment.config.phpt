<?php

/**
 * Test: Nette\Environment configuration.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../bootstrap.php';



class Factory
{
	static function createService($context, $options)
	{
		TestHelpers::note( 'Factory::createService', __METHOD__ );
		Assert::same( array('anyValue' => 'hello world'), $options );
		return (object) NULL;
	}
}

Environment::setName(Environment::PRODUCTION);
Environment::loadConfig('config.ini');
Assert::same(array('Factory::createService'), TestHelpers::fetchNotes());

Assert::same( 'hello world', Environment::getVariable('foo') );

Assert::same( 'hello world', constant('HELLO_WORLD') );

Assert::equal( Nette\ArrayHash::from(array(
	'mbstring-internal_encoding' => 'UTF-8',
	'date.timezone' => 'Europe/Prague',
	'iconv.internal_encoding' => 'UTF-8',
)), Environment::getConfig('php') );

Assert::equal( Nette\ArrayHash::from(array(
	'adapter' => 'pdo_mysql',
	'params' => array(
		'host' => 'db.example.com',
		'username' => 'dbuser',
		'password' => 'secret',
		'dbname' => 'dbname',
	),
)), Environment::getConfig('database') );

Assert::true( Environment::isProduction() );
