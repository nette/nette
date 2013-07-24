<?php

/**
 * Test: Nette\Loaders\RobotLoader filters.
 *
 * @author     David Grudl
 * @package    Nette\Loaders
 */

use Nette\Loaders\RobotLoader,
	Nette\Caching\Storages\DevNullStorage,
	Nette\Caching\Storages\PhpFileStorage;


require __DIR__ . '/../bootstrap.php';


$loader = new RobotLoader;
$loader->setCacheStorage(new DevNullStorage, new PhpFileStorage(TEMP_DIR));
$loader->addDirectory(__DIR__ . '/files');

$loader->filters['php'] = function($input) {
	$code = '';
	foreach (@token_get_all($input) as $token) {
		if (!is_array($token)) {
			$code .= $token;
		} elseif ($token[0] !== T_FINAL) {
			$code .= $token[1];
		}
	}
	return $code;
};

$loader->register(TRUE);


$rc = new ReflectionClass('FinalClass');
Assert::false( $rc->isFinal() );
Assert::false( $rc->getMethod('finalMethod')->isFinal() );
