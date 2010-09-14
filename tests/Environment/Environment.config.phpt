<?php

/**
 * Test: Nette\Environment configuration.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../initialize.php';



class Factory
{
	static function createService($options)
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

Assert::same( array(
	'mbstring-internal_encoding' => 'UTF-8',
	'date.timezone' => 'Europe/Prague',
	'iconv.internal_encoding' => 'UTF-8',
), Environment::getConfig('php')->toArray() );

Assert::same( array(
	'adapter' => 'pdo_mysql',
	'params' => array(
		'host' => 'db.example.com',
		'username' => 'dbuser',
		'password' => 'secret',
		'dbname' => 'dbname',
	),
), Environment::getConfig('database')->toArray() );

Assert::true( Environment::isProduction() );
