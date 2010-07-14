<?php

/**
 * Test: Nette\Config\ConfigAdapterIni section.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../initialize.php';



$config = Config::fromFile('config1.ini', 'development');
T::dump( $config );



__halt_compiler() ?>

------EXPECT------
%ns%Config(
	"database" => %ns%Config(
		"params" => %ns%Config(
			"host" => "dev.example.com"
			"username" => "devuser"
			"password" => "devsecret"
			"dbname" => "dbname"
		)
		"adapter" => "pdo_mysql"
	)
	"timeout" => "10"
	"display_errors" => "1"
	"html_errors" => ""
	"items" => %ns%Config(
		"0" => "10"
		"1" => "20"
	)
	"webname" => "the example"
)
