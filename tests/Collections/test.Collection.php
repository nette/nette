<h1>Nette::Collections::Collection test</h1>

<pre>
<?php
require_once '../../Nette/Debug.php';
require_once '../../Nette/Collections/Collection.php';

/*use Nette::Collections::Collection;*/
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



// Collection::__construct()
$collection = new Collection(NULL, ':numeric');

// ICollection::add()
echo "Adding numeric\n";
$collection->add('10.3');
echo "Adding numeric\n";
$collection->add(12.2);

try {
    echo "Adding non-numeric\n";
    $collection->add('hello');
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}





// Collection::__construct()
$collection = new Collection(NULL, 'Person');

$jack = new Person('Jack');
$mary = new Person('Mary');
$larry = new Person('Larry');
$foo = new ArrayObject();


// ICollection::add()
echo "Adding Jack\n";
$collection->add($jack);
echo "Adding Mary\n";
$collection->add($mary);
echo "Adding Larry\n";
$collection->add($larry);

try {
    echo "Adding foo\n";
    $collection->add($foo);
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}



// ICollection::remove
echo "Removing Larry\n";
Debug::dump($collection->remove($larry));

echo "Removing Larry second time\n";
Debug::dump($collection->remove($larry));

try {
    echo "Removing foo\n";
    Debug::dump($collection->remove($foo));
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}


// ICollection::contains
echo "Contains Jack?\n";
Debug::dump($collection->contains($jack));

echo "Contains Larry?\n";
Debug::dump($collection->contains($larry));

try {
    echo "Contains foo?\n";
    Debug::dump($collection->contains($foo));
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}



// ICollection::count
echo "Count: ", $collection->count(), "\n";
echo "Count: ", count($collection), "\n";


// ICollection::getIterator
echo "Get Interator:\n";
foreach ($collection as & $person) {
    $person->sayHi();
    $person = 10; // try modify
}


// ICollection::toArray()
Debug::dump($collection->toArray());



// ICollection::clear
echo "Clearing\n";
$collection->clear();

foreach ($collection as $person) {
    $person->sayHi();
}



// Collection::__construct()
try {
    echo "Construct from collection\n";
    $collection = new Collection(array($jack, $mary, $foo));
    $collection2 = new Collection($collection, 'Person');
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}


try {
    echo "Construct from array\n";
    $arr = array($jack, $mary, $foo);
    $collection2 = new Collection($arr, 'Person');
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}


// readonly collection
echo "Construct as readonly\n";
$collection2 = new Collection($arr);
$collection2->setReadOnly();
Debug::dump($collection2->isReadOnly());

try {
    echo "Adding Jack\n";
    $collection2->add($jack);
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}

try {
    echo "Removing Jack\n";
    $collection2->remove($jack);
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}

try {
    echo "Clearing\n";
    $collection2->clear();
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
}
