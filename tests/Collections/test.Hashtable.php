<h1>Nette::Collections::Hashtable test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Collections::Hashtable;*/
/*use Nette::Debug;*/



class Person
{
	private $name;


	public function __construct($name)
	{
		$this->name = $name;
	}



	public function sayHi()
	{
		echo "My name is $this->name\n";
	}

}



// Hashtable::__construct()
$hashtable = new Hashtable(NULL, 'Person');

$jack = new Person('Jack');
$mary = new Person('Mary');
$larry = new Person('Larry');
$foo = new ArrayObject();


// IMap::add()
echo "Adding Jack\n";
$hashtable->add('jack', $jack);

try {
	echo "Adding invalid key\n";
	$hashtable->add($foo, $foo);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Adding foo\n";
	$hashtable->add('foo', $foo);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Adding Mary using []\n";
	$hashtable[] = $mary;
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


// IMap::offsetSet()
echo "Adding Mary using ['mary']\n";
$hashtable['mary'] = $mary;

try {
	echo "Adding Mary twice using ['mary']\n";
	$hashtable['mary'] = $mary;
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Adding Mary twice using add()\n";
	$hashtable->add('mary', $mary);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Adding Mary twice using __set()\n";
	$hashtable->mary = $mary;
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Adding Jack using append\n";
	$hashtable->append($jack);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}




// (array) IMap
echo "(array):\n";
Debug::dump((array) $hashtable);


// IMap::getKeys()
echo "getKeys:\n";
Debug::dump($hashtable->getKeys());



// IMap::contains
echo "Contains Jack?\n";
Debug::dump($hashtable->contains($jack));

echo "Contains Mary?\n";
Debug::dump($hashtable->contains($mary));

try {
	echo "Contains foo?\n";
	Debug::dump($hashtable->contains($foo));
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


// IMap::offsetExists
echo "Contains ['jim']?\n";
Debug::dump(isset($hashtable['jim']));

echo "Contains ['jack']?\n";
Debug::dump(isset($hashtable['jack']));

echo "Contains ['mary']?\n";
Debug::dump(isset($hashtable['mary']));



// IMap::__isset
echo "Contains ->jim?\n";
Debug::dump(isset($hashtable->jim));

echo "Contains ->jack?\n";
Debug::dump(isset($hashtable->jack));

echo "Contains ->mary?\n";
Debug::dump(isset($hashtable->mary));



// IMap::offsetGet
try {
	echo "Getting ['jim']\n";
	Debug::dump($hashtable['jim']);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

// IMap::offsetGet
try {
	echo "Getting ['jim'] with throwKeyNotFound\n";
	$hashtable->throwKeyNotFound();
	Debug::dump($hashtable['jim']);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Getting ['mary']\n";
	Debug::dump($hashtable['mary']);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


// IMap::__get
try {
	echo "Getting ->jim\n";
	Debug::dump($hashtable->jim);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Getting ->mary\n";
	Debug::dump($hashtable->mary);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



// IMap::get()
try {
	echo "Getting get('jim')\n";
	Debug::dump($hashtable->get('jim', 'default'));
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Getting get('mary')\n";
	Debug::dump($hashtable->get('mary', 'default'));
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



// IMap::count
echo "Count: ", $hashtable->count(), "\n";
echo "Count: ", count($hashtable), "\n";


// IMap::getIterator
echo "Get Interator:\n";
foreach ($hashtable as $key => $person) {
	echo $key, ' => ', $person->sayHi();
}




// IMap::remove
echo "Removing Jack\n";
Debug::dump($hashtable->remove($jack));

echo "Removing Jack second time\n";
Debug::dump($hashtable->remove($jack));


// IMap::offsetUnset
try {
	echo "Removing using unset(['unknown'])\n";
	unset($hashtable['unknown']);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


// IMap::__unset
try {
	echo "Removing using unset(->unknown)\n";
	unset($hashtable->unknown);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


// IList::search()
echo "search Jack:\n";
Debug::dump($hashtable->search($jack));

echo "search Mary:\n";
Debug::dump($hashtable->search($mary));


// IMap::clear
echo "Clearing\n";
$hashtable->clear();

foreach ($hashtable as $person) {
	$person->sayHi();
}


// List::__construct()
$arr = array('a' => $jack, 'b' => $mary,  'c' => $foo);
try {
	echo "Construct from array\n";
	$hashtable2 = new Hashtable($arr, 'Person');
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

echo "Construct from array II.\n";
$hashtable2 = new Hashtable($arr);
Debug::dump($hashtable2);



// readonly collection
echo "Construct as readonly\n";
$hashtable2 = new Hashtable($hashtable);
$hashtable2->setReadOnly();
Debug::dump($hashtable2->isReadOnly());

try {
	echo "Adding Jack using []\n";
	$hashtable2['new'] = $jack;
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Adding Jack using add\n";
	$hashtable2->add('new', $jack);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Removing using unset\n";
	unset($hashtable2['jack']);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Changing using []\n";
	$hashtable2['jack'] = $jack;
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


$merge = array_merge((array) $hashtable, (array) $hashtable2);
