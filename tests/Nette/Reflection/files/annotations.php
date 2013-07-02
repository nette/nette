<?php

namespace Test;


/**
 * @author john
 */
class AnnotatedClass1
{
	/** @var a */
	public $a;

	/** @var b */
	public static $b;

	/** @var c */
	static public $c;

	/** @var d */
	protected $d;

	/** @var e */
	var $e;
	var $f;

	/** @var const */
	const G = 0;
	var $g;


	/** @return a */
	public function a()
	{}

	/** @return b */
	public static function b()
	{}

	/** @return c */
	static public function c()
	{}

	/** @return d */
	protected function d()
	{}

	/** @return e */
	function e()
	{}
	function f()
	{}

	/** @return g */
	function & g()
	{}


	/**#@+ @multiple */
	var $m1;
	var $m2;
	/**#@-*/

}


/** @out of class */
function a()
{}


/**
 * @author jack
 */
class AnnotatedClass2
{}


class AnnotatedClass3
{}
