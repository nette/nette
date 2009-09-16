<?php

/**
 * Test: Nette\Web\Uri query manipulation.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\Uri;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$uri = new Uri('http://hostname/path?arg=value');
dump( $uri->query ); // 'arg=value'

$uri->appendQuery(NULL);
dump( $uri->query ); // 'arg=value'

$uri->appendQuery(array(NULL));
dump( $uri->query ); // 'arg=value'

$uri->appendQuery('arg2=value2');
dump( $uri->query ); // 'arg=value&arg2=value2'

$uri->appendQuery(array('arg3' => 'value3'));
dump( $uri->query ); // 'arg=value&arg2=value2&arg3=value3'

$uri->setQuery(array('arg3' => 'value3'));
dump( $uri->query ); // 'arg3=value3'



__halt_compiler();

------EXPECT------
string(9) "arg=value"

string(9) "arg=value"

string(9) "arg=value"

string(21) "arg=value&arg2=value2"

string(33) "arg=value&arg2=value2&arg3=value3"

string(11) "arg3=value3"
