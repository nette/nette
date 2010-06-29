<?php

/**
 * Test: Nette\Web\Uri canonicalize.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Uri;



require __DIR__ . '/../initialize.php';



$uri = new Uri('http://hostname/path?arg=value&arg2=v%20a%26l%3Du%2Be');
Assert::same( 'arg=value&arg2=v%20a%26l%3Du%2Be',  $uri->query );

$uri->canonicalize();
Assert::same( 'arg=value&arg2=v a%26l%3Du%2Be',  $uri->query );
