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
"Factory::createService"

array(
	"anyValue" => "hello world"
)

Variable foo: "hello world"

Constant HELLO_WORLD: "hello world"

php.ini config: %ns%Config(
	"mbstring-internal_encoding" => "UTF-8"
	"date.timezone" => "Europe/Prague"
	"iconv.internal_encoding" => "UTF-8"
)

Database config: %ns%Config(
	"adapter" => "pdo_mysql"
	"params" => %ns%Config(
		"host" => "db.example.com"
		"username" => "dbuser"
		"password" => "secret"
		"dbname" => "dbname"
	)
)

is production mode? TRUE
