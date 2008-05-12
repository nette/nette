<h1>Nette::Collections::Set test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Collections::Set;*/
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



// Set::__construct()
$set = new Set(NULL, 'Person');

$jack = new Person('Jack');
$mary = new Person('Mary');
$larry = new Person('Larry');
$foo = new ArrayObject();


// ISet::add()
echo "Adding Jack\n";
Debug::dump($set->add($jack));
echo "Adding Mary\n";
Debug::dump($set->add($mary));
echo "Adding Mary second time\n";
Debug::dump($set->add($mary));
echo "Adding Larry\n";
Debug::dump($set->add($larry));

try {
	echo "Adding foo\n";
	Debug::dump($set->add($foo));
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



// ISet::remove
echo "Removing Larry\n";
Debug::dump($set->remove($larry));

echo "Removing Larry second time\n";
Debug::dump($set->remove($larry));

try {
	echo "Removing foo\n";
	Debug::dump($set->remove($foo));
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


// ISet::contains
echo "Contains Jack?\n";
Debug::dump($set->contains($jack));

echo "Contains Larry?\n";
Debug::dump($set->contains($larry));

try {
	echo "Contains foo?\n";
	Debug::dump($set->contains($foo));
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



// ISet::count
echo "Count: ", $set->count(), "\n";
echo "Count: ", count($set), "\n";


// ISet::getIterator
echo "Get Interator:\n";
foreach ($set as $person) {
	$person->sayHi();
}


// ISet::toArray()
Debug::dump($set->toArray());



// ISet::clear
echo "Clearing\n";
$set->clear();

foreach ($set as $person) {
	$person->sayHi();
}





// Set::__construct()
$set = new Set();

// ISet::add()
echo "Adding 'Jack'\n";
Debug::dump($set->add('Jack'));
echo "Adding 'Mary'\n";
Debug::dump($set->add('Mary'));
echo "Adding 'Mary' second time\n";
Debug::dump($set->add('Mary'));
echo "Adding 'Larry'\n";
Debug::dump($set->add('Larry'));

// ISet::remove
echo "Removing 'Larry'\n";
Debug::dump($set->remove('Larry'));

echo "Removing 'Larry' second time\n";
Debug::dump($set->remove('Larry'));

// ISet::toArray()
Debug::dump($set->toArray());




// Set::__construct()
try {
	echo "Construct from collection\n";
	$set = new Set(array($jack, $mary, $foo));
	$set2 = new Set($set, 'Person');
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


try {
	echo "Construct from array\n";
	$arr = array($jack, $mary, $foo);
	$set2 = new Set($arr, 'Person');
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


// readonly collection
echo "Construct as readonly\n";
$set2 = new Set($arr);
$set2->setReadOnly();
Debug::dump($set2->isReadOnly());

try {
	echo "Adding Jack\n";
	Debug::dump($set2->add($jack));
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Removing Jack\n";
	$set2->remove($jack);
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
	echo "Clearing\n";
	$set2->clear();
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
