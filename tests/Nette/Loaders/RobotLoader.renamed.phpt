<?php

/**
 * Test: Nette\Loaders\RobotLoader and renamed classes.
 *
 * @author     David Grudl
 * @package    Nette\Loaders
 */

use Nette\Loaders\RobotLoader,
	Nette\Caching\Storages\DevNullStorage;


require __DIR__ . '/../bootstrap.php';


$loader = new RobotLoader;
$loader->setCacheStorage(new DevNullStorage);
$loader->addDirectory(TEMP_DIR);

$dir = realpath(TEMP_DIR) . DIRECTORY_SEPARATOR;
file_put_contents($dir . 'file1.php', '<?php class A {}');
file_put_contents($dir . 'file2.php', '<?php class B {}');

$loader->register();

Assert::equal(array(
	'A' => $dir . 'file1.php',
	'B' => $dir . 'file2.php',
), $loader->getIndexedClasses());


rename($dir . 'file1.php', $dir . 'file3.php');

$loader->rebuild();

Assert::equal(array(
	'A' => $dir . 'file3.php',
	'B' => $dir . 'file2.php',
), $loader->getIndexedClasses());


sleep(2); // filemtime resolution is in seconds
file_put_contents($dir . 'file1.php', '<?php class B {}');
file_put_contents($dir . 'file2.php', '<?php ');

$loader->rebuild();

Assert::equal(array(
	'A' => $dir . 'file3.php',
	'B' => $dir . 'file1.php',
), $loader->getIndexedClasses());
