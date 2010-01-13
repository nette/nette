<?php

/**
 * Test: Nette\Annotations and user file parsing.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\Annotations, Nette\Environment;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/files/annotations.php';



// temporary directory
define('TEMP_DIR', dirname(__FILE__) . '/tmp');
NetteTestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);


Annotations::$useReflection = FALSE;


output('AnnotatedClass1');

$rc = new ReflectionClass(/*Nette\*/'AnnotatedClass1');
dump( Annotations::getAll($rc) );
dump( Annotations::getAll($rc->getProperty('a')), '$a' );
dump( Annotations::getAll($rc->getProperty('b')), '$b' );
dump( Annotations::getAll($rc->getProperty('c')), '$c' );
dump( Annotations::getAll($rc->getProperty('d')), '$d' );
dump( Annotations::getAll($rc->getProperty('e')), '$e' );
dump( Annotations::getAll($rc->getProperty('f')), '$f' );
//dump( Annotations::getAll($rc->getProperty('g')), '$g' ); // ignore due PHP bug #50174
dump( Annotations::getAll($rc->getMethod('a')), 'a()' );
dump( Annotations::getAll($rc->getMethod('b')), 'b()' );
dump( Annotations::getAll($rc->getMethod('c')), 'c()' );
dump( Annotations::getAll($rc->getMethod('d')), 'd()' );
dump( Annotations::getAll($rc->getMethod('e')), 'e()' );
dump( Annotations::getAll($rc->getMethod('f')), 'f()' );
dump( Annotations::getAll($rc->getMethod('g')), 'g()' );

output('AnnotatedClass2');

$rc = new ReflectionClass(/*Nette\*/'AnnotatedClass2');
dump( Annotations::getAll($rc) );

output('AnnotatedClass3');

$rc = new ReflectionClass(/*Nette\*/'AnnotatedClass3');
dump( Annotations::getAll($rc) );



__halt_compiler();

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
