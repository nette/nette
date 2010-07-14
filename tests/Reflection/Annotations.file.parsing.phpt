<?php

/**
 * Test: Annotations file parser.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\AnnotationsParser,
	Nette\Environment;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/files/annotations.php';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
T::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);


AnnotationsParser::$useReflection = FALSE;


T::note('AnnotatedClass1');

$rc = new ReflectionClass('Nette\AnnotatedClass1');
T::dump( AnnotationsParser::getAll($rc) );
T::dump( AnnotationsParser::getAll($rc->getProperty('a')), '$a' );
T::dump( AnnotationsParser::getAll($rc->getProperty('b')), '$b' );
T::dump( AnnotationsParser::getAll($rc->getProperty('c')), '$c' );
T::dump( AnnotationsParser::getAll($rc->getProperty('d')), '$d' );
T::dump( AnnotationsParser::getAll($rc->getProperty('e')), '$e' );
T::dump( AnnotationsParser::getAll($rc->getProperty('f')), '$f' );
//T::dump( AnnotationsParser::getAll($rc->getProperty('g')), '$g' ); // ignore due PHP bug #50174
T::dump( AnnotationsParser::getAll($rc->getMethod('a')), 'a()' );
T::dump( AnnotationsParser::getAll($rc->getMethod('b')), 'b()' );
T::dump( AnnotationsParser::getAll($rc->getMethod('c')), 'c()' );
T::dump( AnnotationsParser::getAll($rc->getMethod('d')), 'd()' );
T::dump( AnnotationsParser::getAll($rc->getMethod('e')), 'e()' );
T::dump( AnnotationsParser::getAll($rc->getMethod('f')), 'f()' );
T::dump( AnnotationsParser::getAll($rc->getMethod('g')), 'g()' );

T::note('AnnotatedClass2');

$rc = new ReflectionClass('Nette\AnnotatedClass2');
T::dump( AnnotationsParser::getAll($rc) );

T::note('AnnotatedClass3');

$rc = new ReflectionClass('Nette\AnnotatedClass3');
T::dump( AnnotationsParser::getAll($rc) );



__halt_compiler() ?>

------EXPECT------
AnnotatedClass1

array(
	"author" => array(
		"john"
	)
)

$a: array(
	"var" => array(
		"a"
	)
)

$b: array(
	"var" => array(
		"b"
	)
)

$c: array(
	"var" => array(
		"c"
	)
)

$d: array(
	"var" => array(
		"d"
	)
)

$e: array(
	"var" => array(
		"e"
	)
)

$f: array()

a(): array(
	"return" => array(
		"a"
	)
)

b(): array(
	"return" => array(
		"b"
	)
)

c(): array(
	"return" => array(
		"c"
	)
)

d(): array(
	"return" => array(
		"d"
	)
)

e(): array(
	"return" => array(
		"e"
	)
)

f(): array()

g(): array(
	"return" => array(
		"g"
	)
)

AnnotatedClass2

array(
	"author" => array(
		"jack"
	)
)

AnnotatedClass3

array()
