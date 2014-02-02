<?php

/**
 * Test: Nette\Templating\Helpers::safeUrl()
 *
 * @author     David Grudl
 */

use Nette\Templating\Helpers,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Assert::same( '', Helpers::safeUrl('') );
Assert::same( '', Helpers::safeUrl('http://') );
Assert::same( 'http://x', Helpers::safeUrl('http://x') );
Assert::same( 'http://x:80', Helpers::safeUrl('http://x:80') );
Assert::same( '', Helpers::safeUrl('http://nette.org@1572395127') );
Assert::same( 'https://x', Helpers::safeUrl('https://x') );
Assert::same( 'ftp://x', Helpers::safeUrl('ftp://x') );
Assert::same( 'mailto:x', Helpers::safeUrl('mailto:x') );
Assert::same( '/', Helpers::safeUrl('/') );
Assert::same( '/a:b', Helpers::safeUrl('/a:b') );
Assert::same( '//x', Helpers::safeUrl('//x') );
Assert::same( '#aa:b', Helpers::safeUrl('#aa:b') );
Assert::same( '', Helpers::safeUrl('data:') );
Assert::same( '', Helpers::safeUrl('javascript:') );
Assert::same( '', Helpers::safeUrl(' javascript:') );
Assert::same( 'javascript', Helpers::safeUrl('javascript') );
