<?php

/**
 * Test: Nette\Latte\Runtime\Filters::safeUrl()
 *
 * @author     David Grudl
 */

use Nette\Latte\Runtime\Filters,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( '', Filters::safeUrl('') );
Assert::same( '', Filters::safeUrl('http://') );
Assert::same( 'http://x', Filters::safeUrl('http://x') );
Assert::same( 'http://x:80', Filters::safeUrl('http://x:80') );
Assert::same( '', Filters::safeUrl('http://nette.org@1572395127') );
Assert::same( 'https://x', Filters::safeUrl('https://x') );
Assert::same( 'ftp://x', Filters::safeUrl('ftp://x') );
Assert::same( 'mailto:x', Filters::safeUrl('mailto:x') );
Assert::same( '/', Filters::safeUrl('/') );
Assert::same( '/a:b', Filters::safeUrl('/a:b') );
Assert::same( '//x', Filters::safeUrl('//x') );
Assert::same( '#aa:b', Filters::safeUrl('#aa:b') );
Assert::same( '', Filters::safeUrl('data:') );
Assert::same( '', Filters::safeUrl('javascript:') );
Assert::same( '', Filters::safeUrl(' javascript:') );
Assert::same( 'javascript', Filters::safeUrl('javascript') );
