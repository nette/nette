<h1>Nette\Annotations test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Annotations;*/
/*use Nette\Debug;*/



/**
 * @title(value ="Johno's addendum", mode=True)
 * @title( value= 'One, Two', mode= true or false)
 * @title( value = 'Three (Four)', mode = 'false')
 * @components(game, form, item 1, item 2)
 * @persistent(true)
 * @persistent(FALSE)
 * @renderable
 */
class TestClass {

	/** @secured(role = "admin", level = 2) */
	public $foo;

	/** @RolesAllowed('admin', web editor) */
	public function bar()
	{}

}


$rc = new ReflectionClass('TestClass');
$rp = new ReflectionProperty('TestClass', 'foo');
$rm = new ReflectionMethod('TestClass', 'bar');


echo "\n\n<h2>Class annotations</h2>\n";
Debug::dump( $tmp = Annotations::getAll($rc) );

echo 'Cache test: ';
Debug::dump($tmp === Annotations::getAll($rc));
echo 'Cache test: ';
Debug::dump($tmp === Annotations::getAll(new ReflectionClass('ReflectionClass')));


echo "\n\n<h2>Property annotations</h2>\n";
Debug::dump( Annotations::getAll($rp) );


echo "\n\n<h2>Method annotations</h2>\n";
Debug::dump( Annotations::getAll($rm) );


echo "\n\n<h2>Undefined annotation</h2>\n";

echo "has('xxx'):\n";
Debug::dump( Annotations::has($rc, 'xxx') );

echo "get('xxx'):\n";
Debug::dump( Annotations::get($rc, 'xxx') );

echo "getAll('xxx'):\n";
Debug::dump( Annotations::getAll($rc, 'xxx') );


echo "\n\n<h2>Defined annotation</h2>\n";

echo "has('title'):\n";
Debug::dump( Annotations::has($rc, 'title') );

echo "get('title'):\n";
Debug::dump( Annotations::get($rc, 'title') );

echo "getAll('title'):\n";
Debug::dump( Annotations::getAll($rc, 'title') );


echo "\n\n<h2>Bool annotation</h2>\n";


echo "has('renderable'):\n";
Debug::dump( Annotations::has($rc, 'renderable') );

echo "get('renderable'):\n";
Debug::dump( Annotations::get($rc, 'renderable') );

echo "getAll('renderable'):\n";
Debug::dump( Annotations::getAll($rc, 'renderable') );

echo "get('persistent'):\n";
Debug::dump( Annotations::get($rc, 'persistent') );

echo "getAll('persistent'):\n";
Debug::dump( Annotations::getAll($rc, 'persistent') );
