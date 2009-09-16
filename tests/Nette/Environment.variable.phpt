<?php

/**
 * Test: Nette\Environment variables.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\Environment;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



dump( Environment::getVariable('foo'), "Getting variable 'foo':" );

try {
	dump( Environment::getVariable('tempDir'), "Getting variable 'tempDir':" );

} catch (Exception $e) {
	dump( $e );
}


output("Defining constant 'APP_DIR':");
define('APP_DIR', '/myApp');

dump( Environment::getVariable('appDir'), "Getting variable 'appDir':" );

dump( Environment::getVariable('tempDir'), "Getting variable 'tempDir' #2:" );


output("Setting variable 'test'...");
Environment::setVariable('test', '%appDir%/test');

dump( Environment::getVariable('test'), "Getting variable 'test':" );

dump( Environment::getVariables(), "Getting variables:" );


try {
	output("Setting circular variables...");
	Environment::setVariable('bar', '%foo%');
	Environment::setVariable('foo', '%foobar%');
	Environment::setVariable('foobar', '%bar%');

	dump( Environment::getVariable('bar'), "Getting circular variable:" );

} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
Getting variable 'foo': NULL

Exception InvalidStateException: Unknown environment variable 'appDir'.

Defining constant 'APP_DIR':

Getting variable 'appDir': string(6) "/myApp"

Getting variable 'tempDir' #2: string(11) "/myApp/temp"

Setting variable 'test'...

Getting variable 'test': string(11) "/myApp/test"

Getting variables: array(11) {
	"encoding" => string(5) "UTF-8"
	"lang" => string(2) "en"
	"cacheBase" => string(11) "/myApp/temp"
	"tempDir" => string(11) "/myApp/temp"
	"logDir" => string(10) "/myApp/log"
	"templatesDir" => string(16) "/myApp/templates"
	"presentersDir" => string(17) "/myApp/presenters"
	"componentsDir" => string(17) "/myApp/components"
	"modelsDir" => string(13) "/myApp/models"
	"appDir" => string(6) "/myApp"
	"test" => string(11) "/myApp/test"
}

Setting circular variables...

Exception InvalidStateException: Circular reference detected for variables: foo, foobar, bar.
