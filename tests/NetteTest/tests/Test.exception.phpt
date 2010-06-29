<?php

/**
 * Test: unfinished test due exception.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require __DIR__ . '/initialize.php';



throw new Exception('Something went wrong');



__halt_compiler() ?>

------EXPECT------
Error: Uncaught exception 'Exception' with message 'Something went wrong' in %a%
Stack trace:
#0 {main}