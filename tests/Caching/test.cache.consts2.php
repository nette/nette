<h1>Nette\Caching\Cache constant dependency test (continue...)</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Caching\Cache;*/
/*use Nette\Debug;*/

$key = 'nette';
$value = 'rulez';
$tmpDir = dirname(__FILE__) . '/tmp';

$cache = new Cache(new /*Nette\Caching\*/FileStorage($tmpDir));


echo "'Deleting' dependent const\n";

echo "Is cached?\n";
Debug::dump(isset($cache[$key]));
