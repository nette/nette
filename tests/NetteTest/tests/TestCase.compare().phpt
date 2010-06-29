<?php

/**
 * Test: TestCase::compare() mask
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require __DIR__ . '/initialize.php';



echo '
Any: string string

Non-whitespace: string string

Int: 0, +10, -10

Float: 0.1, +0.1, -0.123

Char: a b c d

Whitespace: ab bb	cc

Regexp: 123abc 23c
';



__halt_compiler() ?>

------EXPECT------

Any: %a%

Non-whitespace: %S% %S?%%S%

Int: %d%, %i%, %i%

Float: %f%, %f%, %f%

Char: %c%%c%b %c%%c%d

Whitespace: a%s?%b%s%bb%s%cc

Regexp: %[^a]%abc%[^c]%c
