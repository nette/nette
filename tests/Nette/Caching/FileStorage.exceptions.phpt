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



Assert::throws(function() {
	new FileStorage(TEMP_DIR . '/missing');
}, 'Nette\DirectoryNotFoundException', "Directory '%a%' not found.");



Assert::throws(function() {
	$storage = new FileStorage(TEMP_DIR);
	$storage->write('a', 'b', array(Cache::TAGS => 'c'));
}, 'Nette\InvalidStateException', 'CacheJournal has not been provided.');
