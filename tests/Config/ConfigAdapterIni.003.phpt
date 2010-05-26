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



require __DIR__ . '/../NetteTest/initialize.php';



output("Load INI");
$config = Config::fromFile('config2.ini');
dump( $config );

output("Save INI");
$config->save('tmp/cfg.ini');
readfile('tmp/cfg.ini');


output("Save section to INI");
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');


output("Load section from INI");
$config = Config::fromFile('config2.ini', 'development');
dump( $config );

output("Save INI");
$config->display_errors = true;
$config->html_errors = false;
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');



__halt_compiler() ?>
