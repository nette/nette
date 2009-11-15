<?php

/**
 * Test: Nette\Annotations with class annotations.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\Annotations;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



/**
 * @title(value ="Johno's addendum", mode=True,) , out
 * @title( value= 'One, Two', mode= true or false)
 * @title( value = 'Three (Four)', mode = 'false')
 * @components(item 1)
 * @persistent(true)
 * @persistent(FALSE)
 * @persistent(null)
 * @author true
 * @author FALSE
 * @author null
 * @author
 * @author John Doe
 * @renderable
 */
class TestClass {

	/** @secured(role = "admin", level = 2) */
	public $foo;

	/** @RolesAllowed('admin', web editor) */
	public function foo()
	{}

}



output('==> Class annotations');

$rc = new ReflectionClass('TestClass');
$tmp = Annotations::getAll($rc);

dump( $tmp["title"][0]->value ); // "Johno's addendum"
dump( $tmp["title"][0]->mode ); // True
dump( $tmp["title"][1]->value ); // "One, Two"
dump( $tmp["title"][1]->mode ); // "true or false"
dump( $tmp["title"][2]->value ); // "Three (Four)"
dump( $tmp["title"][2]->mode ); // "false"
dump( $tmp["components"][0] ); // "item 1"
dump( $tmp["persistent"][0], 'persistent' ); // True
dump( $tmp["persistent"][1] ); // False
dump( $tmp["persistent"][2] ); // Null
dump( $tmp["author"][0], 'author' ); // True
dump( $tmp["author"][1] ); // False
dump( $tmp["author"][2] ); // Null
dump( $tmp["author"][3] ); // True
dump( $tmp["author"][4] ); // "John Doe"
dump( $tmp["renderable"][0] ); // True

dump( $tmp === Annotations::getAll($rc), 'cache test' );
dump( $tmp !== Annotations::getAll(new ReflectionClass('ReflectionClass')), 'cache test' );

dump( Annotations::has($rc, 'title'), "has('title')" ); // True
dump( Annotations::get($rc, 'title')->value ); // "Three (Four)"
dump( Annotations::get($rc, 'title')->mode ); // "false"

$tmp = Annotations::getAll($rc, 'title');
dump( $tmp[0]->value ); // "Johno's addendum"
dump( $tmp[0]->mode ); // True
dump( $tmp[1]->value ); // "One, Two",
dump( $tmp[1]->mode ); // "true or false"
dump( $tmp[2]->value ); // "Three (Four)"
dump( $tmp[2]->mode ); // "false"

dump( Annotations::has($rc, 'renderable'), "has('renderable')" ); // True
dump( Annotations::get($rc, 'renderable'), "get('renderable')" ); // True
$tmp = Annotations::getAll($rc, 'renderable');
dump( $tmp[0] ); // True
$tmp = Annotations::getAll($rc, 'persistent');
dump( Annotations::get($rc, 'persistent'), "get('persistent')" ); // Null
dump( $tmp[0] ); // True
dump( $tmp[1] ); // False
dump( $tmp[2] ); // Null

dump( Annotations::has($rc, 'xxx'), "has('xxx')" ); // False
dump( Annotations::get($rc, 'xxx'), "get('xxx')" ); // Null


output('==> Method annotations');

$rm = new ReflectionMethod('TestClass', 'foo');
$tmp = Annotations::getAll($rm);

dump( $tmp["RolesAllowed"][0][0] ); // 'admin'
dump( $tmp["RolesAllowed"][0][1] ); // 'web editor'


output('==> Property annotations');

$rp = new ReflectionProperty('TestClass', 'foo');
$tmp = Annotations::getAll($rp);

dump( $tmp["secured"][0]->role ); // "admin"
dump( $tmp["secured"][0]->level ); // 2



__halt_compiler();

------EXPECT------
==> Class annotations

string(16) "Johno's addendum"

bool(TRUE)

string(8) "One, Two"

string(13) "true or false"

string(12) "Three (Four)"

string(5) "false"

string(6) "item 1"

persistent: bool(TRUE)

bool(FALSE)

NULL

author: bool(TRUE)

bool(FALSE)

NULL

bool(TRUE)

string(8) "John Doe"

bool(TRUE)

cache test: bool(TRUE)

cache test: bool(TRUE)

has('title'): bool(TRUE)

string(12) "Three (Four)"

string(5) "false"

string(16) "Johno's addendum"

bool(TRUE)

string(8) "One, Two"

string(13) "true or false"

string(12) "Three (Four)"

string(5) "false"

has('renderable'): bool(TRUE)

get('renderable'): bool(TRUE)

bool(TRUE)

get('persistent'): NULL

bool(TRUE)

bool(FALSE)

NULL

has('xxx'): bool(FALSE)

get('xxx'): NULL

==> Method annotations

string(5) "admin"

string(10) "web editor"

==> Property annotations

string(5) "admin"

int(2)
