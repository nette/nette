<h1>Nette::Caching::Cache & Memcached expiration test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Caching::Cache;*/
/*use Nette::Debug;*/

$key = 'nette';
$value = 'rulez';

$cache = new Cache(new /*Nette::Caching::*/MemcachedStorage('localhost'));


echo "Writing cache...\n";
$cache->save($key, $value, array(
	'expire' => time() + 2,
));


for($i = 0; $i < 4; $i++) {
	echo "Sleeping 1 second\n";
	sleep(1);
	echo "Is cached?";
	Debug::dump(isset($cache[$key]));
}
