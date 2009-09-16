<?php

/**
 * Test: Nette\Web\Uri malformed URI.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\Uri;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$uri = new Uri(':');



__halt_compiler();

------EXPECT------
Exception InvalidArgumentException: Malformed or unsupported URI ':'.
