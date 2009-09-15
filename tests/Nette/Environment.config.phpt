<?php

/**
 * Test: Environment configuration.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



class Factory
{
	static function createService($options)
	{
		dump( __METHOD__ );
		dump( $options );
		return (object) NULL;
	}
}

Environment::setName(Environment::PRODUCTION);
Environment::loadConfig('config.ini');

dump( Environment::getVariable('foo'), "Variable foo:" );

dump( constant('HELLO_WORLD'), "Constant HELLO_WORLD:" );

dump( Environment::getConfig('php'), "php.ini config:" );

dump( Environment::getConfig('database'), "Database config:" );

dump( Environment::isProduction(), "is production mode?" );



__halt_compiler();

------EXPECT------
string(22) "Factory::createService"

array(1) {
	"anyValue" => string(11) "hello world"
}

Variable foo: string(11) "hello world"

Constant HELLO_WORLD: string(11) "hello world"

php.ini config: object(%ns%Config) (3) {
	"mbstring-internal_encoding" => string(5) "UTF-8"
	"date.timezone" => string(13) "Europe/Prague"
	"iconv.internal_encoding" => string(5) "UTF-8"
}

Database config: object(%ns%Config) (2) {
	"adapter" => string(9) "pdo_mysql"
	"params" => object(%ns%Config) (4) {
		"host" => string(14) "db.example.com"
		"username" => string(6) "dbuser"
		"password" => string(6) "secret"
		"dbname" => string(6) "dbname"
	}
}

is production mode? bool(TRUE)
