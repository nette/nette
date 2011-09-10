<?php

/**
 * Test: Nette\Diagnostics\Debugger::dump() basic types in HTML and text mode.
 *
 * @author     David Grudl
 * @package    Nette\Diagnostics
 * @subpackage UnitTests
 */

use Nette\Diagnostics\Debugger;



require __DIR__ . '/../bootstrap.php';



Debugger::$productionMode = FALSE;



class Test
{
	public $x = array(10, NULL);

	private $y = 'hello';

	protected $z = 30;
}


// HTML mode

Debugger::$consoleMode = FALSE;

Assert::match( '<pre class="nette-dump"><span class="php-null">NULL</span>
</pre>', Debugger::dump(NULL, TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-bool">TRUE</span>
</pre>', Debugger::dump(TRUE, TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-bool">FALSE</span>
</pre>', Debugger::dump(FALSE, TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-int">0</span>
</pre>', Debugger::dump(0, TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-int">1</span>
</pre>', Debugger::dump(1, TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-float">0.0</span>
</pre>', Debugger::dump(0.0, TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-float">0.1</span>
</pre>', Debugger::dump(0.1, TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-string">""</span>
</pre>', Debugger::dump('', TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-string">"0"</span>
</pre>', Debugger::dump('0', TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-string">"\\x00"</span>
</pre>', Debugger::dump("\x00", TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-array">array</span>(5) <code>[
   <span class="php-key">0</span> => <span class="php-int">1</span>
   <span class="php-key">1</span> => <span class="php-string">"hello"</span> (5)
   <span class="php-key">2</span> => <span class="php-array">array</span>(0)
   <span class="php-key">3</span> => <span class="php-array">array</span>(2) <code>[
      <span class="php-key">0</span> => <span class="php-int">1</span>
      <span class="php-key">1</span> => <span class="php-int">2</span>
   ]</code>
   <span class="php-key">4</span> => <span class="php-array">array</span>(2) <code>{
      <span class="php-key">1</span> => <span class="php-int">1</span>
      <span class="php-key">2</span> => <span class="php-int">2</span>
   }</code>
]</code>
</pre>
', Debugger::dump(array(1, 'hello', array(), array(1, 2), array(1 => 1, 2)), TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-resource">stream resource</span>
</pre>', Debugger::dump(fopen(__FILE__, 'r'), TRUE) );

Assert::match( '<pre class="nette-dump"><span class="php-object">stdClass</span>(0)
</pre>', Debugger::dump((object) NULL, TRUE) );

$obj = new Test;
Assert::same(Debugger::dump($obj), $obj);


// Text mode

Debugger::$consoleMode = TRUE;

Assert::match( 'NULL', Debugger::dump(NULL, TRUE) );

Assert::match( 'TRUE', Debugger::dump(TRUE, TRUE) );

Assert::match( 'FALSE', Debugger::dump(FALSE, TRUE) );

Assert::match( '0', Debugger::dump(0, TRUE) );

Assert::match( '1', Debugger::dump(1, TRUE) );

Assert::match( '0.0', Debugger::dump(0.0, TRUE) );

Assert::match( '0.1', Debugger::dump(0.1, TRUE) );

Assert::match( '""', Debugger::dump('', TRUE) );

Assert::match( '"0"', Debugger::dump('0', TRUE) );

Assert::match( '"\\x00"', Debugger::dump("\x00", TRUE) );

Assert::match( 'array(5) [
   0 => 1
   1 => "hello" (5)
   2 => array(0)
   3 => array(2) [
      0 => 1
      1 => 2
   ]
   4 => array(2) {
      1 => 1
      2 => 2
   }
]
', Debugger::dump(array(1, 'hello', array(), array(1, 2), array(1 => 1, 2)), TRUE) );

Assert::match( 'stream resource', Debugger::dump(fopen(__FILE__, 'r'), TRUE) );

Assert::match( 'stdClass(0)', Debugger::dump((object) NULL, TRUE) );

Assert::match( 'Test(3) {
   x => array(2) [
      0 => 10
      1 => NULL
   ]
   y private => "hello" (5)
   z protected => 30
}
', Debugger::dump($obj, TRUE) );
