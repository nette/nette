<h1>Nette\Collections\Set test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Collections\Set;*/
/*use Nette\Debug;*/



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
$set = new Set(NULL, ':numeric');

// ISet::append()
echo "Adding numeric\n";
$set->append('10.3');
echo "Adding numeric\n";
$set->append(12.2);

try {
	echo "Adding non-numeric\n";
	$set->append('hello');
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}





// Set::__construct()
$set = new Set(NULL, 'Person');

$jack = new Person('Jack');
$mary = new Person('Mary');
$larry = new Person('Larry');
$foo = new ArrayObject();


// ISet::append()
echo "Adding Jack\n";
Debug::dump($set->append($jack));
echo "Adding Mary\n";
Debug::dump($set->append($mary));
echo "Adding Mary second time\n";
Debug::dump($set->append($mary));
echo "Adding Larry\n";
Debug::dump($set->append($larry));

try {
	echo "Adding foo\n";
	Debug::dump($set->append($foo));
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
foreach ($set as & $person) {
	$person->sayHi();
	$person = 10; // try modify
}


// (array) ISet
echo "(array):\n";
Debug::dump((array) $set);



// ISet::clear
echo "Clearing\n";
$set->clear();

foreach ($set as $person) {
	$person->sayHi();
}





// Set::__construct()
$set = new Set();

// ISet::append()
echo "Adding 'Jack'\n";
Debug::dump($set->append('Jack'));
echo "Adding 'Mary'\n";
Debug::dump($set->append('Mary'));
echo "Adding 'Mary' second time\n";
Debug::dump($set->append('Mary'));
echo "Adding 'Larry'\n";
Debug::dump($set->append('Larry'));

// ISet::remove
echo "Removing 'Larry'\n";
Debug::dump($set->remove('Larry'));

echo "Removing 'Larry' second time\n";
Debug::dump($set->remove('Larry'));

// (array) ISet
Debug::dump((array) $set);




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
	Debug::dump($set2->append($jack));
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

foreach ($set2 as $key => & $val) {
	$val = FALSE;
}
echo "Contains Jack?\n";
Debug::dump($set2->contains($jack));
