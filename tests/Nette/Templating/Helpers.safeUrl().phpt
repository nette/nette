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
Assert::same( 'https://x', Helpers::safeUrl('https://x') );
Assert::same( 'ftp://x', Helpers::safeUrl('ftp://x') );
Assert::same( 'mailto:x', Helpers::safeUrl('mailto:x') );
Assert::same( '/', Helpers::safeUrl('/') );
Assert::same( '', Helpers::safeUrl('data:') );
Assert::same( '', Helpers::safeUrl('javascript:') );
Assert::same( '', Helpers::safeUrl(' javascript:') );
Assert::same( 'javascript', Helpers::safeUrl('javascript') );
