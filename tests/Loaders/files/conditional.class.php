<?php

namespace Nette;

use Nette;

$a = 1;
${'a'} = "{$a} ${a}";

if (FALSE) {
	class Object
	{
	}
}

class TestClass
{
}
