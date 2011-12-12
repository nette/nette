<?php

/**
 * Test: Nette\Config\Loader: including files
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config;



require __DIR__ . '/../bootstrap.php';



$config = new Config\Loader;
$data = $config->load('files/config.includes.neon', 'production');

Assert::same( array(
	dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'config.includes.neon',
	dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'config.child.ini',
	dirname(__FILE__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'config.child.php',
), $config->getDependencies() );

Assert::same( array(
	'parameters' => array(
		'me' => array(
			'config.child.ini',
			'config.child.php',
		),
		'scalar' => 1,
		'list' => array(5, 6, 1, 2),
		'force' => array(1, 2),
	),
), $data );
