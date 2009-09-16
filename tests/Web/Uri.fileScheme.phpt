<?php

/**
 * Test: Nette\Web\Uri file://
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\Uri;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$uri = new Uri('file://localhost/D:/dokumentace/rfc3986.txt');
dump( (string) $uri ); // 'file://localhost/D:/dokumentace/rfc3986.txt'
dump( $uri->scheme ); // 'file'
dump( $uri->user ); // ''
dump( $uri->pass ); // ''
dump( $uri->host ); // 'localhost'
dump( $uri->port ); // NULL
dump( $uri->path ); // '/D:/dokumentace/rfc3986.txt'
dump( $uri->query ); // ''
dump( $uri->fragment ); // ''


$uri = new Uri('file:///D:/dokumentace/rfc3986.txt');
dump( (string) $uri ); // 'file://D:/dokumentace/rfc3986.txt'
dump( $uri->scheme ); // 'file'
dump( $uri->user ); // ''
dump( $uri->pass ); // ''
dump( $uri->host ); // ''
dump( $uri->port ); // NULL
dump( $uri->path ); // 'D:/dokumentace/rfc3986.txt'
dump( $uri->query ); // ''
dump( $uri->fragment ); // ''



__halt_compiler();

------EXPECT------
string(43) "file://localhost/D:/dokumentace/rfc3986.txt"

string(4) "file"

string(0) ""

string(0) ""

string(9) "localhost"

NULL

string(27) "/D:/dokumentace/rfc3986.txt"

string(0) ""

string(0) ""

string(33) "file://D:/dokumentace/rfc3986.txt"

string(4) "file"

string(0) ""

string(0) ""

string(0) ""

NULL

string(26) "D:/dokumentace/rfc3986.txt"

string(0) ""

string(0) ""
