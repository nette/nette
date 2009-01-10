<h1>Nette\SmartCachingIterator test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\SmartCachingIterator;*/


echo "\n<h2>Two items in array</h2>\n";


$arr = array('Nette', 'Framework');

foreach ($iterator = new SmartCachingIterator($arr) as $k => $v)
{
	echo "\n";
	echo "first: "; Debug::dump($iterator->isFirst());
	echo "last: "; Debug::dump($iterator->isLast());
	echo "counter: "; Debug::dump($iterator->getCounter());

	foreach ($innerIterator = new SmartCachingIterator($arr) as $k => $v)
	{
		echo "\n";
		echo "  inner first: "; Debug::dump($innerIterator->isFirst());
		echo "  inner last: "; Debug::dump($innerIterator->isLast());
		echo "  inner counter: "; Debug::dump($innerIterator->getCounter());
	}
}

$iterator->rewind();
echo "rewinding...\n";
echo "first: "; Debug::dump($iterator->isFirst());
echo "last: "; Debug::dump($iterator->isLast());
echo "counter: "; Debug::dump($iterator->getCounter());
echo "empty: "; Debug::dump($iterator->isEmpty());



echo "\n<h2>One item in array</h2>\n";

$arr = array('Nette');

foreach ($iterator = new SmartCachingIterator($arr) as $k => $v)
{
	echo "\n";
	echo "first: "; Debug::dump($iterator->isFirst());
	echo "last: "; Debug::dump($iterator->isLast());
	echo "counter: "; Debug::dump($iterator->getCounter());
}

$iterator->rewind();
echo "rewinding...\n";
echo "first: "; Debug::dump($iterator->isFirst());
echo "last: "; Debug::dump($iterator->isLast());
echo "counter: "; Debug::dump($iterator->getCounter());
echo "empty: "; Debug::dump($iterator->isEmpty());



echo "\n<h2>Zero item in array</h2>\n";

$arr = array();

$iterator = new SmartCachingIterator($arr);
$iterator->next();
$iterator->next();
echo "first: "; Debug::dump($iterator->isFirst());
echo "last: "; Debug::dump($iterator->isLast());
echo "counter: "; Debug::dump($iterator->getCounter());
echo "empty: "; Debug::dump($iterator->isEmpty());
