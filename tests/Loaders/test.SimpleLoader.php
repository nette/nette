<h1>Nette::Loaders::SimpleLoader test</h1>

<pre>
<?php

require_once '../../Nette/Loaders/SimpleLoader.php';
/*use Nette::Debug;*/

set_include_path('../../');

$loader = new /*Nette::Loaders::*/SimpleLoader;
$loader->register();

/**/AutoLoader::load('Nette::Debug');/**/

Debug::dump('class Nette::Debug successfully loaded');