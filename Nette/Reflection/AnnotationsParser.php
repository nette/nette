<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Reflection;

use Nette,
	Nette\Utils\Strings;


/**
 * Annotations support for PHP.
 *
 * @author     David Grudl
 * @Annotation
 */
class AnnotationsParser
{
	/** @internal single & double quoted PHP string */
	const RE_STRING = '\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';

	/** @internal identifier */
	const RE_IDENTIFIER = '[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF-\\\]*';

	/** @var bool */
	public static $useReflection;

	/** @var array */
	public static $inherited = array('description', 'param', 'return');

	/** @var array */
	private static $cache;

	/** @var array */
	private static $timestamps;

	/** @var Nette\Caching\IStorage */
	private static $cacheStorage;


	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}


	/**
	 * Returns annotations.
	 * @param  \ReflectionClass|\ReflectionMethod|\ReflectionProperty
	 * @return array
	 */
	public static function getAll(\Reflector $r)
	{
		if ($r instanceof \ReflectionClass) {
			$type = $r->getName();
			$member = 'class';

		} elseif ($r instanceof \ReflectionMethod) {
			$type = $r->getDeclaringClass()->getName();
			$member = $r->getName();

		} else {
			$type = $r->getDeclaringClass()->getName();
			$member = '$' . $r->getName();
		}

		if (!self::$useReflection) { // auto-expire cache
			$file = $r instanceof \ReflectionClass ? $r->getFileName() : $r->getDeclaringClass()->getFileName(); // will be used later
			if ($file && isset(self::$timestamps[$file]) && self::$timestamps[$file] !== filemtime($file)) {
				unset(self::$cache[$type]);
			}
			unset(self::$timestamps[$file]);
		}

		if (isset(self::$cache[$type][$member])) { // is value cached?
			return self::$cache[$type][$member];
		}

		if (self::$useReflection === NULL) { // detects whether is reflection available
			self::$useReflection = (bool) ClassType::from(__CLASS__)->getDocComment();
		}

		if (self::$useReflection) {
			$annotations = self::parseComment($r->getDocComment());

		} else {
			if (!self::$cacheStorage) {
				// trigger_error('Set a cache storage for annotations parser via Nette\Reflection\AnnotationParser::setCacheStorage().', E_USER_WARNING);
				self::$cacheStorage = new Nette\Caching\Storages\DevNullStorage;
			}
			$outerCache = new Nette\Caching\Cache(self::$cacheStorage, 'Nette.Reflection.Annotations');

			if (self::$cache === NULL) {
				self::$cache = (array) $outerCache->load('list');
				self::$timestamps = isset(self::$cache['*']) ? self::$cache['*'] : array();
			}

			if (!isset(self::$cache[$type]) && $file) {
				self::$cache['*'][$file] = filemtime($file);
				foreach (self::parsePhp(file_get_contents($file)) as $class => $info) {
					foreach ($info as $prop => $comment) {
						if ($prop !== 'use') {
							self::$cache[$class][$prop] = self::parseComment($comment);
						}
					}
				}
				$outerCache->save('list', self::$cache);
			}

			if (isset(self::$cache[$type][$member])) {
				$annotations = self::$cache[$type][$member];
			} else {
				$annotations = array();
			}
		}

		if ($r instanceof \ReflectionMethod && !$r->isPrivate()
			&& (!$r->isConstructor() || !empty($annotations['inheritdoc'][0]))
		) {
			try {
				$inherited = self::getAll(new \ReflectionMethod(get_parent_class($type), $member));
			} catch (\ReflectionException $e) {
				try {
					$inherited = self::getAll($r->getPrototype());
				} catch (\ReflectionException $e) {
					$inherited = array();
				}
			}
			$annotations += array_intersect_key($inherited, array_flip(self::$inherited));
		}

		return self::$cache[$type][$member] = $annotations;
	}


	/**
	 * Expands class name into FQN.
	 * @param  string
	 * @return string  fully qualified class name
	 * @throws Nette\InvalidArgumentException
	 */
	public static function expandClassName($name, \ReflectionClass $reflector)
	{
		if (empty($name)) {
			throw new Nette\InvalidArgumentException('Class name must not be empty.');
		}

		if ($name[0] === '\\') { // already fully qualified
			return ltrim($name, '\\');
		}

		$parsed = static::parsePhp(file_get_contents($reflector->getFileName()));
		$uses = array_change_key_case((array) $tmp = & $parsed[$reflector->getName()]['use']);
		$parts = explode('\\', $name, 2);
		$parts[0] = strtolower($parts[0]);
		if (isset($uses[$parts[0]])) {
			$parts[0] = $uses[$parts[0]];
			return implode('\\', $parts);

		} elseif ($reflector->inNamespace()) {
			return $reflector->getNamespaceName() . '\\' . $name;

		} else {
			return $name;
		}
	}


	/**
	 * Parses phpDoc comment.
	 * @param  string
	 * @return array
	 */
	private static function parseComment($comment)
	{
		static $tokens = array('true' => TRUE, 'false' => FALSE, 'null' => NULL, '' => TRUE);

		$res = array();
		$comment = preg_replace('#^\s*\*\s?#ms', '', trim($comment, '/*'));
		$parts = preg_split('#^\s*(?=@'.self::RE_IDENTIFIER.')#m', $comment, 2);

		$description = trim($parts[0]);
		if ($description !== '') {
			$res['description'] = array($description);
		}

		$matches = Strings::matchAll(
			isset($parts[1]) ? $parts[1] : '',
			'~
				(?<=\s|^)@('.self::RE_IDENTIFIER.')[ \t]*      ##  annotation
				(
					\((?>'.self::RE_STRING.'|[^\'")@]+)+\)|  ##  (value)
					[^(@\r\n][^@\r\n]*|)                     ##  value
			~xi'
		);

		foreach ($matches as $match) {
			list(, $name, $value) = $match;

			if (substr($value, 0, 1) === '(') {
				$items = array();
				$key = '';
				$val = TRUE;
				$value[0] = ',';
				while ($m = Strings::match(
					$value,
					'#\s*,\s*(?>(' . self::RE_IDENTIFIER . ')\s*=\s*)?(' . self::RE_STRING . '|[^\'"),\s][^\'"),]*)#A')
				) {
					$value = substr($value, strlen($m[0]));
					list(, $key, $val) = $m;
					$val = rtrim($val);
					if ($val[0] === "'" || $val[0] === '"') {
						$val = substr($val, 1, -1);

					} elseif (is_numeric($val)) {
						$val = 1 * $val;

					} else {
						$lval = strtolower($val);
						$val = array_key_exists($lval, $tokens) ? $tokens[$lval] : $val;
					}

					if ($key === '') {
						$items[] = $val;

					} else {
						$items[$key] = $val;
					}
				}

				$value = count($items) < 2 && $key === '' ? $val : $items;

			} else {
				$value = trim($value);
				if (is_numeric($value)) {
					$value = 1 * $value;

				} else {
					$lval = strtolower($value);
					$value = array_key_exists($lval, $tokens) ? $tokens[$lval] : $value;
				}
			}

			$class = $name . 'Annotation';
			if (class_exists($class)) {
				$res[$name][] = new $class(is_array($value) ? $value : array('value' => $value));

			} else {
				$res[$name][] = is_array($value) ? Nette\ArrayHash::from($value) : $value;
			}
		}

		return $res;
	}


	/**
	 * Parses PHP file.
	 * @param  string
	 * @return array [class => [prop => comment (or 'use' => [alias => class])]
	 */
	public static function parsePhp($code)
	{
		if (Strings::match($code, '#//nette'.'loader=(\S*)#')) {
			return; // TODO: allways ignore?
		}

		$tokens = @token_get_all($code);
		$namespace = $class = $classLevel = $level = $docComment = NULL;
		$res = $uses = array();

		while (list($key, $token) = each($tokens)) {
			switch (is_array($token) ? $token[0] : $token) {
				case T_DOC_COMMENT:
					$docComment = $token[1];
					break;

				case T_NAMESPACE:
					$namespace = self::fetch($tokens, array(T_STRING, T_NS_SEPARATOR)) . '\\';
					$uses = array();
					break;

				case T_CLASS:
				case T_INTERFACE:
				case PHP_VERSION_ID < 50400 ? -1 : T_TRAIT:
					if ($name = self::fetch($tokens, T_STRING)) {
						$class = $namespace . $name;
						$classLevel = $level + 1;
						if ($docComment) {
							$res[$class]['class'] = $docComment;
						}
						if ($uses) {
							$res[$class]['use'] = $uses;
						}
					}
					break;

				case T_FUNCTION:
					self::fetch($tokens, '&');
					if ($level === $classLevel && $docComment && ($name = self::fetch($tokens, T_STRING))) {
						$res[$class][$name] = $docComment;
					}
					break;

				case T_VAR:
				case T_PUBLIC:
				case T_PROTECTED:
					self::fetch($tokens, T_STATIC);
					if ($level === $classLevel && $docComment && ($name = self::fetch($tokens, T_VARIABLE))) {
						$res[$class][$name] = $docComment;
					}
					break;

				case T_USE:
					while (!$class && ($name = self::fetch($tokens, array(T_STRING, T_NS_SEPARATOR)))) {
						if (self::fetch($tokens, T_AS)) {
							$uses[self::fetch($tokens, T_STRING)] = ltrim($name, '\\');
						} else {
							$tmp = explode('\\', $name);
							$uses[end($tmp)] = $name;
						}
						if (!self::fetch($tokens, ',')) {
							break;
						}
					}
					break;

				case T_CURLY_OPEN:
				case T_DOLLAR_OPEN_CURLY_BRACES:
				case '{':
					$level++;
					break;

				case '}':
					if ($level === $classLevel) {
						$class = $classLevel = NULL;
					}
					$level--;
					// break omitted
				case ';':
					$docComment = NULL;
			}
		}

		return $res;
	}


	private static function fetch(& $tokens, $take)
	{
		$res = NULL;
		while ($token = current($tokens)) {
			list($token, $s) = is_array($token) ? $token : array($token, $token);
			if (in_array($token, (array) $take, TRUE)) {
				$res .= $s;
			} elseif (!in_array($token, array(T_DOC_COMMENT, T_WHITESPACE, T_COMMENT), TRUE)) {
				break;
			}
			next($tokens);
		}
		return $res;
	}


	/********************* backend ****************d*g**/


	/**
	 * @return void
	 */
	public static function setCacheStorage(Nette\Caching\IStorage $storage)
	{
		self::$cacheStorage = $storage;
	}


	/**
	 * @return Nette\Caching\IStorage
	 */
	public static function getCacheStorage()
	{
		return self::$cacheStorage;
	}

}
