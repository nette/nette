<?php

/**
 * Test: Nette\Web\UriScript parse.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\UriScript;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$uri = new UriScript('http://nettephp.com:8080/file.php?q=search');
dump( $uri->scriptPath ); // NULL
dump( $uri->baseUri ); // 'http://nettephp.com:8080'
dump( $uri->basePath ); // false
dump( $uri->relativeUri ); // 'file.php'
dump( $uri->pathInfo ); // '/file.php'



__halt_compiler();

------EXPECT------
string(0) ""

string(24) "http://nettephp.com:8080"

string(0) ""

string(8) "file.php"

string(9) "/file.php"
