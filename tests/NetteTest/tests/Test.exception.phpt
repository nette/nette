<?php

/**
 * Test: unfinished test due exception.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require dirname(__FILE__) . '/../initialize.php';



throw new Exception('Something went wrong');



__halt_compiler();

------EXPECT------
Error: Uncaught Exception Exception: Something went wrong