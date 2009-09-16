<?php

/**
 * Test: Nette\Web\Uri ftp://
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\Uri;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$uri = new Uri('ftp://ftp.is.co.za/rfc/rfc3986.txt');

dump( $uri->scheme ); // 'ftp'
dump( $uri->user ); // ''
dump( $uri->pass ); // ''
dump( $uri->host ); // 'ftp.is.co.za'
dump( $uri->port ); // 21
dump( $uri->path ); // '/rfc/rfc3986.txt'
dump( $uri->query ); // ''
dump( $uri->fragment ); // ''
dump( $uri->authority ); // 'ftp.is.co.za'
dump( $uri->hostUri ); // 'ftp://ftp.is.co.za'
dump( $uri->absoluteUri ); // 'ftp://ftp.is.co.za/rfc/rfc3986.txt'



__halt_compiler();

------EXPECT------
string(3) "ftp"

string(0) ""

string(0) ""

string(12) "ftp.is.co.za"

int(21)

string(16) "/rfc/rfc3986.txt"

string(0) ""

string(0) ""

string(12) "ftp.is.co.za"

string(18) "ftp://ftp.is.co.za"

string(34) "ftp://ftp.is.co.za/rfc/rfc3986.txt"
