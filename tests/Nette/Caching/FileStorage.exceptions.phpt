<?php

/**
 * Test: Nette\Caching\Storages\FileStorage exception situations.
 *
 * @author     Matej Kravjar
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Caching\Cache,
	Nette\Caching\Storages\FileStorage;



require __DIR__ . '/../bootstrap.php';



// purge temporary directory
TestHelpers::purge(TEMP_DIR);
// do not create cache directory



try {
	new FileStorage(TEMP_DIR . '/cache');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\DirectoryNotFoundException', "Directory '%a%' not found.", $e);
}



// create cache directory
mkdir(TEMP_DIR . '/cache');



try {
	$storage = new FileStorage(TEMP_DIR . '/cache');
	$storage->write('a', 'b', array(Cache::TAGS => 'c'));
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidStateException', 'CacheJournal has not been provided.', $e);
}
