<?php

/**
 * Test: Nette\Config\IniAdapter section.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../bootstrap.php';



$config = Config::fromFile('config1.ini', 'development');
Assert::equal( Nette\ArrayHash::from(array(
	'database' => array(
		'params' => array(
			'host' => 'dev.example.com',
			'username' => 'devuser',
			'password' => 'devsecret',
			'dbname' => 'dbname',
		),
		'adapter' => 'pdo_mysql',
	),
	'timeout' => '10',
	'display_errors' => '1',
	'html_errors' => '',
	'items' => array(
		'0' => '10',
		'1' => '20',
	),
	'webname' => 'the example',
), TRUE), $config );
