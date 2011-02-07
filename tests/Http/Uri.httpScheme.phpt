<?php

/**
 * Test: Nette\Web\Uri http://
 *
 * @author     David Grudl
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Uri;



require __DIR__ . '/../bootstrap.php';



$uri = new Uri('http://username:password@hostname:60/path/script.php?arg=value#anchor');

Assert::same( 'http://hostname:60/path/script.php?arg=value#anchor',  (string) $uri );
Assert::same( 'http',  $uri->scheme );
Assert::same( 'username',  $uri->user );
Assert::same( 'password',  $uri->password );
Assert::same( 'hostname',  $uri->host );
Assert::same( 60,  $uri->port );
Assert::same( '/path/script.php',  $uri->path );
Assert::same( '/path/',  $uri->basePath );
Assert::same( 'arg=value',  $uri->query );
Assert::same( 'anchor',  $uri->fragment );
Assert::same( 'hostname:60',  $uri->authority );
Assert::same( 'http://hostname:60',  $uri->hostUri );
Assert::same( 'http://hostname:60/path/script.php?arg=value#anchor',  $uri->absoluteUri );
Assert::same( 'http://hostname:60/path/',  $uri->baseUri );
Assert::same( 'script.php?arg=value#anchor',  $uri->relativeUri );
