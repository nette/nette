<?php

/**
 * Test: Nette\Collections\ArrayList and extension method.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

/*use Nette\Collections\ArrayList;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Collections.inc';



/*Nette\Object::extensionMethod('Nette\Collections\ICollection::join', function(Nette\Collections\ICollection $that, $separator)*/
/**/function ICollection_prototype_join(ICollection $that, $separator)/**/
{
	return implode($separator, (array) $that);
}/*);*/



$list = new ArrayList(NULL, 'Person');

$list[] = new Person('Jack');
$list[] = new Person('Mary');
$list[] = new Person('Larry');

dump( $list->join(', ') );

// undeclared method
try {
	$list->test();

} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
string(17) "Jack, Mary, Larry"

Exception MemberAccessException: Call to undefined method %ns%ArrayList::test().
