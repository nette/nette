<?php

/**
 * @phpversion 5.4
 */

namespace Test {

	use A\B as C;

	class TestClass1
	{
		use X;
	}

	use D, E;
	use \F\G as H;

	class TestClass2
	{
	}

}

namespace Test2 {

	function () use ($a) {
	};

	class TestClass3
	{
	}

	use A\B\C;

	class TestClass4
	{
	}

}
