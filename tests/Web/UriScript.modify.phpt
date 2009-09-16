<?php

/**
 * Test: Nette\Web\UriScript modify.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\UriScript;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$uri = new UriScript('http://nettephp.com:8080/file.php?q=search');
$uri->path = '/test/';
$uri->scriptPath = '/test/index.php';

dump( $uri->scriptPath ); // '/test/index.php'
dump( $uri->baseUri ); // 'http://nettephp.com:8080/test/'
dump( $uri->basePath ); // '/test/'
dump( $uri->relativeUri ); // ''
dump( $uri->pathInfo ); // ''
dump( $uri->absoluteUri ); // 'http://nettephp.com:8080/test/?q=search'



__halt_compiler();

------EXPECT------
string(15) "/test/index.php"

string(30) "http://nettephp.com:8080/test/"

string(6) "/test/"

string(0) ""

string(0) ""

string(39) "http://nettephp.com:8080/test/?q=search"
