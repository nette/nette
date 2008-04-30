<h1>Nette::Collections::ArrayList test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Collections::ArrayList;*/
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



// ArrayList::__construct()
$list = new ArrayList(NULL, 'Person');

$jack = new Person('Jack');
$mary = new Person('Mary');
$larry = new Person('Larry');
$foo = new ArrayObject();


// IList::add()
echo "Adding Jack\n";
$list->add($jack);
echo "Adding Mary\n";
$list->add($mary);


try {
    echo "Adding foo\n";
    $list->add($foo);
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


// IList::offsetSet()
echo "Adding Jack using []\n";
$list[] = $jack;

try {
    echo "Adding foo using []\n";
    $list[] = $foo;
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


// IList::toArray()
echo "toArray:\n";
Debug::dump($list->toArray());


// IList::insertAt()
echo "Adding Larry using insertAt()\n";
Debug::dump($list->insertAt(0, $larry));


echo "Adding Larry using insertAt()\n";
Debug::dump($list->insertAt(4, $larry));

try {
    echo "Adding Larry using insertAt()\n";
    Debug::dump($list->insertAt(6, $larry));
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



// IList::toArray()
echo "toArray:\n";
Debug::dump($list->toArray());



// IList::contains
echo "Contains Jack?\n";
Debug::dump($list->contains($jack));

echo "Contains Mary?\n";
Debug::dump($list->contains($mary));

try {
    echo "Contains foo?\n";
    Debug::dump($list->contains($foo));
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}


// IList::offsetExists
echo "Contains #-1?\n";
Debug::dump(isset($list[-1]));

echo "Contains #0?\n";
Debug::dump(isset($list[0]));

echo "Contains #5?\n";
Debug::dump(isset($list[5]));



// IList::offsetGet
try {
    echo "Getting #-1\n";
    Debug::dump($list[-1]);
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
    echo "Getting #0\n";
    Debug::dump($list[0]);
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}




// IList::remove
echo "Removing Larry\n";
Debug::dump($list->remove($larry));

echo "Removing Larry second time\n";
Debug::dump($list->remove($larry));



// IList::offsetUnset
try {
    echo "Removing using unset\n";
    unset($list[-1]);
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
    echo "Removing using unset\n";
    unset($list[1]);
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}



// IList::indexOf()
echo "indexOf Jack:\n";
Debug::dump($list->indexOf($jack));

echo "indexOf Mary:\n";
Debug::dump($list->indexOf($mary));



// IList::count
echo "Count: ", $list->count(), "\n";
echo "Count: ", count($list), "\n";


// IList::getIterator
echo "Get Interator:\n";
foreach ($list as $key => $person) {
    echo $key, ' => ', $person->sayHi();
}


// IList::clear
echo "Clearing\n";
$list->clear();

foreach ($list as $person) {
    $person->sayHi();
}



// ArrayList::__construct()
$arr = array('a' => $jack, 'b' => $mary,  'c' => $foo);
try {
    echo "Construct from array\n";
    $list2 = new ArrayList($arr, 'Person');
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

echo "Construct from array II.\n";
$list2 = new ArrayList($arr);
Debug::dump($list2->toArray());



// readonly collection
echo "Construct as readonly\n";
$list2 = new ArrayList($arr);
$list2->setReadOnly();
Debug::dump($list2->isReadOnly());

try {
    echo "Adding Jack using []\n";
    $list2[] = $jack;
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
    echo "Adding Jack using insertAt\n";
    $list2->insertAt(0, $jack);
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
    echo "Removing using unset\n";
    unset($list2[1]);
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}

try {
    echo "Changing using []\n";
    $list2[1] = $jack;
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
