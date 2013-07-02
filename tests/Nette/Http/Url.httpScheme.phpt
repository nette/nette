<?php

/**
 * Test: Nette\Http\Url http://
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Url;


require __DIR__ . '/../bootstrap.php';


$url = new Url('http://username:password@hostname:60/path/script.php?arg=value#anchor');

Assert::same( 'http://hostname:60/path/script.php?arg=value#anchor',  (string) $url );
Assert::same( 'http',  $url->scheme );
Assert::same( 'username',  $url->user );
Assert::same( 'password',  $url->password );
Assert::same( 'hostname',  $url->host );
Assert::same( 60,  $url->port );
Assert::same( '/path/script.php',  $url->path );
Assert::same( '/path/',  $url->basePath );
Assert::same( 'arg=value',  $url->query );
Assert::same( 'anchor',  $url->fragment );
Assert::same( 'hostname:60',  $url->authority );
Assert::same( 'http://hostname:60',  $url->hostUrl );
Assert::same( 'http://hostname:60/path/script.php?arg=value#anchor',  $url->absoluteUrl );
Assert::same( 'http://hostname:60/path/',  $url->baseUrl );
Assert::same( 'script.php?arg=value#anchor',  $url->relativeUrl );

$url->scheme = NULL;
Assert::same( '//username:password@hostname:60/path/script.php?arg=value#anchor',  $url->absoluteUrl );
