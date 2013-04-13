<?php

/**
 * Test: Nette\DI\Config\Loader: including files
 *
 * @author     David Grudl
 * @package    Nette\DI\Config
 */

use Nette\DI\Config;



require __DIR__ . '/../bootstrap.php';



$config = new Config\Loader;
$data = $config->load('files/loader.includes.neon', 'production');

Assert::same( array(
	realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'loader.includes.neon'),
	realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'loader.includes.child.ini'),
	realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'loader.includes.child.php'),
), $config->getDependencies() );

Assert::same( array(
	'parameters' => array(
		'me' => array(
			'loader.includes.child.ini',
			'loader.includes.child.php',
		),
		'scalar' => 1,
		'list' => array(5, 6, 1, 2),
		'force' => array(1, 2),
	),
), $data );
