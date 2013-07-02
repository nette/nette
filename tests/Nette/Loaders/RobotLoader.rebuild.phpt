<?php

/**
 * Test: Nette\Loaders\RobotLoader rebuild only once.
 *
 * @author     David Grudl
 * @package    Nette\Loaders
 */

use Nette\Loaders\RobotLoader,
	Nette\Caching\Storages\DevNullStorage;


require __DIR__ . '/../bootstrap.php';


file_put_contents(TEMP_DIR . '/file1.php', '<?php class A {}');
file_put_contents(TEMP_DIR . '/file2.php', '<?php class B {}');

$loader = new RobotLoader;
$loader->setCacheStorage(new DevNullStorage);
$loader->addDirectory(TEMP_DIR);
$loader->register();

rename(TEMP_DIR . '/file1.php', TEMP_DIR . '/file3.php');

$a = new A;

rename(TEMP_DIR . '/file2.php', TEMP_DIR . '/file4.php');

Assert::false( class_exists('B') );
