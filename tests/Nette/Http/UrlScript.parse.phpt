<?php

/**
 * Test: Nette\Http\UrlScript parse.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\UrlScript;



require __DIR__ . '/../bootstrap.php';



$uri = new UrlScript('http://nette.org:8080/file.php?q=search');
Assert::same( '/', $uri->scriptPath );
Assert::same( 'http://nette.org:8080/',  $uri->baseUri );
Assert::same( '/', $uri->basePath );
Assert::same( 'file.php?q=search',  $uri->relativeUri );
Assert::same( 'file.php',  $uri->pathInfo );
