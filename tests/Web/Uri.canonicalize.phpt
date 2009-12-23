<?php

/**
 * Test: Nette\Web\Uri canonicalize.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\Uri;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$uri = new Uri('http://hostname/path?arg=value&arg2=v%20a%26l%3Du%2Be');
dump( $uri->query ); // 'arg=value&arg2=v%20a%26l%3Du%2Be'

$uri->canonicalize();
dump( $uri->query ); // 'arg2=v a%26l%3Du%2Be&arg=value'



__halt_compiler();

------EXPECT------
string(32) "arg=value&arg2=v%20a%26l%3Du%2Be"

string(30) "arg=value&arg2=v a%26l%3Du%2Be"
