<?php

/**
 * Test: Nette\Collections\ArrayList and extension method.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Collections
 * @subpackage UnitTests
 */

use Nette\Collections\ArrayList;



require __DIR__ . '/../initialize.php';

require __DIR__ . '/Collections.inc';



/**/Nette\Object::extensionMethod('Nette\\Collections\\ICollection::join', function(Nette\Collections\ICollection $that, $separator)/**/
/*5.2* function ICollection_prototype_join(ICollection $that, $separator)*/
{
	return implode($separator, (array) $that);
}/**/);/**/



$list = new ArrayList(NULL, 'Person');

$list[] = new Person('Jack');
$list[] = new Person('Mary');
$list[] = new Person('Larry');

Assert::same( "Jack, Mary, Larry", $list->join(', ') );

// undeclared method
try {
	$list->test();
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('MemberAccessException', "Call to undefined method %ns%ArrayList::test().", $e );
}
