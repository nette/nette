<?php

/**
 * Test: Nette\Environment configuration.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Environment;



require __DIR__ . '/../initialize.php';



class Factory
{
	static function createService($options)
	{
		T::dump( __METHOD__ );
		T::dump( $options );
		return (object) NULL;
	}
}

Environment::setName(Environment::PRODUCTION);
Environment::loadConfig('config.ini');

T::dump( Environment::getVariable('foo'), "Variable foo:" );

T::dump( constant('HELLO_WORLD'), "Constant HELLO_WORLD:" );

T::dump( Environment::getConfig('php'), "php.ini config:" );

T::dump( Environment::getConfig('database'), "Database config:" );

T::dump( Environment::isProduction(), "is production mode?" );



__halt_compiler() ?>

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
