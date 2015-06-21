<?php

/**
 * Test: Nette\Http\Url canonicalize.
 */

use Nette\Http\Url;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$url = new Url('http://hostname/path?arg=value&arg2=v%20a%26l%3Du%2Be');
Assert::same('arg=value&arg2=v%20a%26l%3Du%2Be',  $url->query);
$url->canonicalize();
Assert::same('arg=value&arg2=v a%26l%3Du%2Be',  $url->query);


$url = new Url('http://username%3A:password%3A@hostN%61me:60/p%61th%2f%25%23%3F()?arg=value&arg2=v%20a%26l%3Du%2B%23e#%61nchor');
$url->canonicalize();
Assert::same('http://hostname:60/path%2f%25%23%3F()?arg=value&arg2=v a%26l%3Du%2B%23e#%61nchor', (string) $url);
