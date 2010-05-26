<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Reflection
 */

namespace Nette\Reflection;

use Nette,
	Nette\ObjectMixin;



/**
 * Reports information about a class.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Reflection
 */
class ClassReflection extends \ReflectionClass
{

	/** @var array (method => array(type => callback)) */
	private static $extMethods;



	/**
	 * @param  string|object
	 * @return Nette\Reflection\ClassReflection
	 */
	public static function from($class)
	{
		return new self($class);
	}



	public function __toString()
	{
		return 'Class ' . $this->getName();
	}



	/**
	 * @return bool
	 */
	public function hasEventProperty($name)
	{
		if (preg_match('#^on[A-Z]#', $name) && $this->hasProperty($name)) {
			$rp = $this->getProperty($name);
			return $rp->isPublic() && !$rp->isStatic();
		}
		return FALSE;
	}



	/**
	 * Adds a method to class.
	 * @param  string  method name
	 * @param  mixed   callback or closure
	 * @return ClassReflection  provides a fluent interface
	 */
	public function setExtensionMethod($name, $callback)
	{
		$l = & self::$extMethods[strtolower($name)];
		$l[strtolower($this->getName())] = callback($callback);
		$l[''] = NULL;
		return $this;
	}



	/**
	 * Returns extension method.
	 * @param  string  method name
	 * @return mixed
	 */
	public function getExtensionMethod($name)
	{
		/*5.2* if (self::$extMethods === NULL || $name === NULL) { // for backwards compatibility
			$list = get_defined_functions(); // names are lowercase!
			foreach ($list['user'] as $fce) {
				$pair = explode('_prototype_', $fce);
				if (count($pair) === 2) {
					self::$extMethods[$pair[1]][$pair[0]] = callback($fce);
					self::$extMethods[$pair[1]][''] = NULL;
				}
			}
			if ($name === NULL) return NULL;
		}
		*/

		$class = strtolower($this->getName());
		$l = & self::$extMethods[strtolower($name)];

		if (empty($l)) {
			return FALSE;

		} elseif (isset($l[''][$class])) { // cached value
			return $l[''][$class];
		}

		$cl = $class;
		do {
			if (isset($l[$cl])) {
				return $l[''][$class] = $l[$cl];
			}
		} while (($cl = strtolower(get_parent_class($cl))) !== '');

		foreach (class_implements($class) as $cl) {
			$cl = strtolower($cl);
			if (isset($l[$cl])) {
				return $l[''][$class] = $l[$cl];
			}
		}
		return $l[''][$class] = FALSE;
	}



	/********************* Reflection layer ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassReflection
	 * @internal
	 */
	public static function import(\ReflectionClass $ref)
	{
		return new self($ref->getName());
	}



	/**
	 * @return Nette\Reflection\MethodReflection
	 */
	public function getConstructor()
	{
		return ($ref = parent::getConstructor()) ? MethodReflection::import($ref) : NULL;
	}



	/**
	 * @return Nette\Reflection\ExtensionReflection
	 */
	public function getExtension()
	{
		return ($ref = parent::getExtension()) ? ExtensionReflection::import($ref) : NULL;
	}



	public function getInterfaces()
	{
		return array_map(array('Nette\Reflection\ClassReflection', 'import'), parent::getInterfaces());
	}



	/**
	 * @return Nette\Reflection\MethodReflection
	 */
	public function getMethod($name)
	{
		return MethodReflection::import(parent::getMethod($name));
	}



	public function getMethods($filter = -1)
	{
		return array_map(array('Nette\Reflection\MethodReflection', 'import'), parent::getMethods($filter));
	}



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public function getParentClass()
	{
		return ($ref = parent::getParentClass()) ? self::import($ref) : NULL;
	}



	public function getProperties($filter = -1)
	{
		return array_map(array('Nette\Reflection\PropertyReflection', 'import'), parent::getProperties($filter));
	}



	/**
	 * @return Nette\Reflection\PropertyReflection
	 */
	public function getProperty($name)
	{
		return PropertyReflection::import(parent::getProperty($name));
	}



	/********************* Nette\Annotations support ****************d*g**/



	/**
	 * Has class specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return !empty($res[$name]);
	}



	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return IAnnotation
	 */
	public function getAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return isset($res[$name]) ? end($res[$name]) : NULL;
	}



	/**
	 * Returns all annotations.
	 * @return array
	 */
	public function getAnnotations()
	{
		return AnnotationsParser::getAll($this);
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassReflection(/*5.2*$this*//**/get_called_class()/**/);
	}



	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		throw new \MemberAccessException("Cannot unset the property {$this->reflection->name}::\$$name.");
	}

}
