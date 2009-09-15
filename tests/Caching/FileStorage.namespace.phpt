<?php

/**
 * Test: FileStorage & namespace test.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

/*use Nette\Caching\Cache;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// temporary directory
$tempDir = dirname(__FILE__) . '/tmp';

NetteTestHelpers::purge($tempDir);


$storage = new /*Nette\Caching\*/FileStorage($tempDir);
$cacheA = new Cache($storage, 'a');
$cacheB = new Cache($storage, 'b');


message('Writing cache...');
$cacheA['key'] = 'hello';
$cacheB['key'] = 'world';

dump( isset($cacheA['key']), 'Is cached #1?' );
dump( isset($cacheB['key']), 'Is cached #2?' );
dump( $cacheA['key'] === 'hello', 'Is cache ok #1?' );
dump( $cacheB['key'] === 'world', 'Is cache ok #2?' );

message('Removing from cache #2 using unset()...');
unset($cacheB['key']);
$cacheA->release();
$cacheB->release();

dump( isset($cacheA['key']), 'Is cached #1?' );
dump( isset($cacheB['key']), 'Is cached #2?' );



__halt_compiler();

------EXPECT------
Writing cache...

Is cached #1? bool(TRUE)

Is cached #2? bool(TRUE)

Is cache ok #1? bool(TRUE)

Is cache ok #2? bool(TRUE)

Removing from cache #2 using unset()...

Is cached #1? bool(TRUE)

Is cached #2? bool(FALSE)
