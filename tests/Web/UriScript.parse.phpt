<?php

/**
 * Test: Nette\Web\UriScript parse.
 *
 * @author     David Grudl
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\UriScript;



require __DIR__ . '/../initialize.php';



$uri = new UriScript('http://nette.org:8080/file.php?q=search');
Assert::same( '', $uri->scriptPath );
Assert::same( 'http://nette.org:8080',  $uri->baseUri );
Assert::same( '', $uri->basePath );
Assert::same( 'file.php',  $uri->relativeUri );
Assert::same( '/file.php',  $uri->pathInfo );
