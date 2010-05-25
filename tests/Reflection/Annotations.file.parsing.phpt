<?php

/**
 * Test: Annotations file parser.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

/*use Nette\Reflection\AnnotationsParser, Nette\Environment;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/files/annotations.php';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);


AnnotationsParser::$useReflection = FALSE;


output('AnnotatedClass1');

$rc = new ReflectionClass(/*Nette\*/'AnnotatedClass1');
dump( AnnotationsParser::getAll($rc) );
dump( AnnotationsParser::getAll($rc->getProperty('a')), '$a' );
dump( AnnotationsParser::getAll($rc->getProperty('b')), '$b' );
dump( AnnotationsParser::getAll($rc->getProperty('c')), '$c' );
dump( AnnotationsParser::getAll($rc->getProperty('d')), '$d' );
dump( AnnotationsParser::getAll($rc->getProperty('e')), '$e' );
dump( AnnotationsParser::getAll($rc->getProperty('f')), '$f' );
//dump( AnnotationsParser::getAll($rc->getProperty('g')), '$g' ); // ignore due PHP bug #50174
dump( AnnotationsParser::getAll($rc->getMethod('a')), 'a()' );
dump( AnnotationsParser::getAll($rc->getMethod('b')), 'b()' );
dump( AnnotationsParser::getAll($rc->getMethod('c')), 'c()' );
dump( AnnotationsParser::getAll($rc->getMethod('d')), 'd()' );
dump( AnnotationsParser::getAll($rc->getMethod('e')), 'e()' );
dump( AnnotationsParser::getAll($rc->getMethod('f')), 'f()' );
dump( AnnotationsParser::getAll($rc->getMethod('g')), 'g()' );

output('AnnotatedClass2');

$rc = new ReflectionClass(/*Nette\*/'AnnotatedClass2');
dump( AnnotationsParser::getAll($rc) );

output('AnnotatedClass3');

$rc = new ReflectionClass(/*Nette\*/'AnnotatedClass3');
dump( AnnotationsParser::getAll($rc) );



__halt_compiler() ?>

------EXPECT------
AnnotatedClass1

array(1) {
	"author" => array(1) {
		0 => string(4) "john"
	}
}

$a: array(1) {
	"var" => array(1) {
		0 => string(1) "a"
	}
}

$b: array(1) {
	"var" => array(1) {
		0 => string(1) "b"
	}
}

$c: array(1) {
	"var" => array(1) {
		0 => string(1) "c"
	}
}

$d: array(1) {
	"var" => array(1) {
		0 => string(1) "d"
	}
}

$e: array(1) {
	"var" => array(1) {
		0 => string(1) "e"
	}
}

$f: array(0)

a(): array(1) {
	"return" => array(1) {
		0 => string(1) "a"
	}
}

b(): array(1) {
	"return" => array(1) {
		0 => string(1) "b"
	}
}

c(): array(1) {
	"return" => array(1) {
		0 => string(1) "c"
	}
}

d(): array(1) {
	"return" => array(1) {
		0 => string(1) "d"
	}
}

e(): array(1) {
	"return" => array(1) {
		0 => string(1) "e"
	}
}

f(): array(0)

g(): array(1) {
	"return" => array(1) {
		0 => string(1) "g"
	}
}

AnnotatedClass2

array(1) {
	"author" => array(1) {
		0 => string(4) "jack"
	}
}

AnnotatedClass3

array(0)
