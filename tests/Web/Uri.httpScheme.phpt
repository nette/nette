<?php

/**
 * Test: Nette\Web\Uri http://
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\Uri;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$uri = new Uri('http://username:password@hostname:60/path?arg=value#anchor');

dump( (string) $uri ); // 'http://hostname:60/path?arg=value#anchor'
dump( $uri->scheme ); // 'http'
dump( $uri->user ); // 'username'
dump( $uri->pass ); // 'password'
dump( $uri->host ); // 'hostname'
dump( $uri->port ); // 60
dump( $uri->path ); // '/path'
dump( $uri->query ); // 'arg=value'
dump( $uri->fragment ); // 'anchor'
dump( $uri->authority ); // 'hostname:60'
dump( $uri->hostUri ); // 'http://hostname:60'
dump( $uri->absoluteUri ); // 'http://hostname:60/path?arg=value#anchor'



__halt_compiler();

------EXPECT------
string(40) "http://hostname:60/path?arg=value#anchor"

string(4) "http"

string(8) "username"

string(8) "password"

string(8) "hostname"

int(60)

string(5) "/path"

string(9) "arg=value"

string(6) "anchor"

string(11) "hostname:60"

string(18) "http://hostname:60"

string(40) "http://hostname:60/path?arg=value#anchor"
