<?php

/**
 * Test: ConfigAdapterIni section.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Config
 * @subpackage UnitTests
 */

/*use Nette\Config\Config;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



section("Load INI");
$config = Config::fromFile('config2.ini');
dump( $config );

section("Save INI");
$config->save('tmp/cfg.ini');
readfile('tmp/cfg.ini');


section("Save section to INI");
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');


section("Load section from INI");
$config = Config::fromFile('config2.ini', 'development', NULL);
dump( $config );

section("Save INI");
$config->display_errors = true;
$config->html_errors = false;
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');



__halt_compiler();
