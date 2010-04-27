<?php

/**
 * Test: Nette\Config\ConfigAdapterIni section.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Config
 * @subpackage UnitTests
 */

/*use Nette\Config\Config;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$config = Config::fromFile('config1.ini', 'development');
dump( $config );



__halt_compiler();

------EXPECT------
object(%ns%Config) (6) {
	"database" => object(%ns%Config) (2) {
		"params" => object(%ns%Config) (4) {
			"host" => string(15) "dev.example.com"
			"username" => string(7) "devuser"
			"password" => string(9) "devsecret"
			"dbname" => string(6) "dbname"
		}
		"adapter" => string(9) "pdo_mysql"
	}
	"timeout" => string(2) "10"
	"display_errors" => string(1) "1"
	"html_errors" => string(0) ""
	"items" => object(%ns%Config) (2) {
		"0" => string(2) "10"
		"1" => string(2) "20"
	}
	"webname" => string(11) "the example"
}
