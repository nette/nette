<h1>Nette::Loaders::RobotLoader test</h1>

<pre>
<?php

require_once '../../Nette/Loaders/RobotLoader.php';
/*use Nette::Debug;*/

$cache = dirname(__FILE__) . '/tmp/autoload.bin';
@unlink($cache);

$loader = new /*Nette::Loaders::*/RobotLoader;
$loader->cacheFile = $cache;
$loader->scanDirs[] = dirname(__FILE__) . '/../../Nette/';
$loader->register();

Debug::dump('class Nette::Debug successfully loaded');