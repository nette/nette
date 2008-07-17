<h1>Nette::Loaders::RobotLoader test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';
/*use Nette::Debug;*/
/*use Nette::Environment;*/

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

foreach (glob(Environment::expand('%tempDir%/*')) as $file) unlink($file); // delete all files


$loader = new /*Nette::Loaders::*/RobotLoader;
$loader->addDirectory(dirname(__FILE__));
$loader->addDirectory(dirname(__FILE__)); // doubled
$loader->register();

if (class_exists(/*Nette::*/'TestClass')) echo 'class TestClass successfully loaded';