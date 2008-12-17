<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Application
 * @version    $Id$
 */

/*namespace Nette\Application;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Application/IRouter.php';



/**
 * The bidirectional route is responsible for mapping
 * HTTP request to a PresenterRoute object for dispatch and vice-versa.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Application
 */
class Route extends /*Nette\*/Object implements IRouter
{
	const PRESENTER_KEY = 'presenter';
	const MODULE_KEY = 'module';

	/** flag */
	const CASE_SENSITIVE = 256;

	/**#@+ uri type */
	const HOST = 1;
	const PATH = 2;
	const RELATIVE = 3;
	/**#@-*/

	/**#@+ key used in {@link Route::$styles} */
	const PATTERN = 'pattern';
	const FILTER_IN = 'filterIn';
	const FILTER_OUT = 'filterOut';
	const FILTER_TABLE = 'filterTable';
	/**#@-*/

	/**#@+ @internal fixity types - how to handle 'default' value? {@link Route::$metadata} */
	const OPTIONAL = 0;
	const PATH_OPTIONAL = 1;
	const CONSTANT = 2;
	/**#@-*/

	/** @var bool */
	public static $defaultFlags = 0;

	/** @var array */
	public static $styles = array(
		'#' => array( // default style for path parameters
			self::PATTERN => '[^/]+',
			self::FILTER_IN => 'rawurldecode',
			self::FILTER_OUT => 'rawurlencode',
		),
		'?#' => array( // default style for query parameters
		),
		'module' => array(
			self::PATTERN => '[a-z][a-z0-9.-]*',
			self::FILTER_IN => /*Nette\Application\*/'Route::path2presenter',
			self::FILTER_OUT => /*Nette\Application\*/'Route::presenter2path',
		),
		'presenter' => array(
			self::PATTERN => '[a-z][a-z0-9.-]*',
			self::FILTER_IN => /*Nette\Application\*/'Route::path2presenter',
			self::FILTER_OUT => /*Nette\Application\*/'Route::presenter2path',
		),
		'view' => array(
			self::PATTERN => '[a-z][a-z0-9-]*',
			self::FILTER_IN => /*Nette\Application\*/'Route::path2view',
			self::FILTER_OUT => /*Nette\Application\*/'Route::view2path',
		),
		'?module' => array(
		),
		'?presenter' => array(
		),
		'?view' => array(
		),
	);

	/** @var string */
	public $mask;

	/** @var array */
	private $sequence;

	/** @var string  regular expression pattern */
	private $re;

	/** @var array of [default & fixity, filterIn, filterOut] */
	protected $metadata = array();

	/** @var array  */
	protected $xlat;

	/** @var int HOST, PATH, RELATIVE */
	protected $type;

	/** @var int */
	protected $flags;



	/**
	 * @param  string  URL mask, e.g. '<presenter>/<view>/<id \d{1,3}>'
	 * @param  array   default values
	 * @param  int     flags
	 */
	public function __construct($mask, array $defaults = array(), $flags = 0)
	{
		$this->flags = $flags | self::$defaultFlags;
		$this->setMask($mask, $defaults);
	}



	/**
	 * Maps HTTP request to a PresenterRequest object.
	 * @param  Nette\Web\IHttpRequest
	 * @return PresenterRequest|NULL
	 */
	public function match(/*Nette\Web\*/IHttpRequest $context)
	{
		// combine with precedence: mask (params in URL-path), fixity, query, (post,) defaults

		// 1) URL MASK
		$uri = $context->getUri();

		if ($this->type === self::HOST) {
			$path = '//' . $uri->host . $uri->path;

		} elseif ($this->type === self::RELATIVE) {
			$basePath = $uri->basePath;
			if (strncmp($uri->path, $basePath, strlen($basePath)) !== 0) {
				return NULL;
			}
			$path = (string) substr($uri->path, strlen($basePath));

		} else {
			$path = $uri->path;
		}

		$path = rtrim($path, '/') . '/';
		$path = /*Nette\*/String::fixEncoding($path);

		if (!preg_match($this->re, $path, $matches)) {
			// stop, not matched
			return NULL;
		}

		// deletes numeric keys, restore '-' chars
		$params = array();
		foreach ($matches as $k => $v) {
			if (is_string($k)) {
				$params[str_replace('___', '-', $k)] = $v; // trick
			}
		}


		// 2) CONSTANT FIXITY
		foreach ($this->metadata as $name => $meta) {
			if (isset($params[$name])) {
				//$params[$name] = $this->flags & self::CASE_SENSITIVE === 0 ? strtolower($params[$name]) : */$params[$name]; // strtolower damages UTF-8

			} elseif (isset($meta['fixity']) && $meta['fixity'] !== self::OPTIONAL) {
				$params[$name] = NULL; // cannot be overwriten in 3) and detected by isset() in 4)
			}
		}


		// 3) QUERY
		if ($this->xlat) {
			$params += self::renameKeys($context->getQuery(), array_flip($this->xlat));
		} else {
			$params += $context->getQuery();
		}


		// 4) APPLY FILTERS & FIXITY
		foreach ($this->metadata as $name => $meta) {
			if (isset($params[$name])) {
				if (isset($meta[self::FILTER_TABLE][$params[$name]])) { // applyies filterTable only to path parameters
					$params[$name] = $meta[self::FILTER_TABLE][$params[$name]];

				} elseif (isset($meta[self::FILTER_IN])) { // applyies filterIn only to path parameters
					$params[$name] = call_user_func($meta[self::FILTER_IN], $params[$name]);
				}

			} elseif (isset($meta['fixity'])) {
				$params[$name] = $meta['default'];
			}
		}


		// 5) BUILD PresenterRequest
		if (!isset($params[self::PRESENTER_KEY])) {
			throw new /*\*/InvalidStateException('Missing presenter in route definition.');
		}
		if (isset($this->metadata[self::MODULE_KEY])) {
			if (!isset($params[self::MODULE_KEY])) {
				throw new /*\*/InvalidStateException('Missing module in route definition.');
			}
			$presenter = $params[self::MODULE_KEY] . ':' . $params[self::PRESENTER_KEY];
			unset($params[self::MODULE_KEY], $params[self::PRESENTER_KEY]);

		} else {
			$presenter = $params[self::PRESENTER_KEY];
			unset($params[self::PRESENTER_KEY]);
		}

		return new PresenterRequest(
			$presenter,
			$context->getMethod(),
			$params,
			$context->getPost(),
			$context->getFiles(),
			array('secured' => $context->isSecured())
		);
	}



	/**
	 * Constructs URL path from PresenterRequest object.
	 * @param  Nette\Web\IHttpRequest
	 * @param  PresenterRequest
	 * @return string|NULL
	 */
	public function constructUrl(PresenterRequest $request, /*Nette\Web\*/IHttpRequest $context)
	{
		if ($this->flags & self::ONE_WAY) {
			return NULL;
		}

		$params = $request->getParams();
		$metadata = $this->metadata;

		$presenter = $request->getPresenterName();
		if (isset($metadata[self::MODULE_KEY])) {
			if (isset($metadata[self::MODULE_KEY]['fixity'])) {
				$a = strlen($metadata[self::MODULE_KEY]['default']);
				if (substr($presenter, $a, 1) !== ':') {
					return NULL; // module not match
				}
			} else {
				$a = strrpos($presenter, ':');
			}
			$params[self::MODULE_KEY] = substr($presenter, 0, $a);
			$params[self::PRESENTER_KEY] = substr($presenter, $a + 1);
		} else {
			$params[self::PRESENTER_KEY] = $presenter;
		}

		foreach ($metadata as $name => $meta) {
			if (!isset($params[$name])) continue; // retains NULL values

			if (isset($meta['fixity'])) {
				if (strcasecmp($params[$name], $meta['default']) === 0) {  // intentionally ==
					// remove default values; NULL values are retain
					unset($params[$name]);
					continue;

				} elseif ($meta['fixity'] === self::CONSTANT) {
					return NULL; // missing or wrong parameter '$name'
				}
			}

			if (isset($meta['filterTable2'][$params[$name]])) {
				$params[$name] = $meta['filterTable2'][$params[$name]];

			} elseif (isset($meta[self::FILTER_OUT])) {
				$params[$name] = call_user_func($meta[self::FILTER_OUT], $params[$name]);
			}

			if (isset($meta[self::PATTERN]) && !preg_match($meta[self::PATTERN], $params[$name])) {
				return NULL; // pattern not match
			}
		}

		// compositing path
		$sequence = $this->sequence;
		$optional = TRUE;
		$uri = '';
		$i = count($sequence) - 1;
		do {
			$uri = $sequence[$i] . $uri;
			if ($i === 0) break;
			$i--;

			$name = $sequence[$i]; $i--; // parameter name

			if ($name[0] === '?') { // "foo" parameter
				continue;

			} elseif (isset($params[$name]) && $params[$name] != '') { // intentionally ==
				$optional = FALSE;
				$uri = $params[$name] . $uri;
				unset($params[$name]);

			} elseif (isset($metadata[$name]['fixity'])) { // has default value?
				if ($optional) {
					$uri = '';

				} elseif ($metadata[$name]['default'] == '') { // intentionally ==
					if ($uri[0] === '/' && substr($sequence[$i], -1) === '/') {
						return NULL; // default value is empty but is required
					}

				} else {
					$uri = $metadata[$name]['defOut'] . $uri;
				}

			} else {
				return NULL; // missing parameter '$name'
			}
		} while (TRUE);


		// build query string
		if ($this->xlat) {
			$params = self::renameKeys($params, $this->xlat);
		}

		$query = http_build_query($params, '', '&');
		if ($query !== '') $uri .= '?' . $query;

		// absolutize path
		if ($this->type === self::RELATIVE) {
			$uri = $context->getUri()->basePath . $uri;
		}

		if ($this->flags & self::SECURED) {
			$uri = $this->type === self::HOST ? 'https:' . $uri : 'https://' . $context->getUri()->authority . $uri;

		} elseif ($this->type === self::HOST) {
			$uri = $context->getUri()->scheme . ':' . $uri; // required due bug in IE7
		}

		return $uri;
	}



	/**
	 * Parse mask and array of default values; initializes object.
	 * @param  string
	 * @param  array
	 * @return void
	 */
	private function setMask($mask, array $defaults)
	{
		$this->mask = $mask;

		// detect '//host/path' vs. '/abs. path' vs. 'relative path'
		if (substr($mask, 0, 2) === '//') {
			$this->type = self::HOST;

		} elseif (substr($mask, 0, 1) === '/') {
			$this->type = self::PATH;

		} else {
			$this->type = self::RELATIVE;
		}

		$metadata = array();
		foreach ($defaults as $name => $def) {
			$metadata[$name] = array(
				'default' => $def,
				'fixity' => self::CONSTANT
			);
		}


		// 1) PARSE QUERY PART OF MASK
		$this->xlat = array();
		$pos = strpos($mask, ' ? ');
		if ($pos !== FALSE) {
			preg_match_all(
				'/(?:([a-zA-Z0-9_.-]+)=)?<([^># ]+) *([^>#]*)(#?[^>]*)>/', // name=<parameter-name [pattern][#class]>
				substr($mask, $pos + 1),
				$matches,
				PREG_SET_ORDER
			);
			$mask = rtrim(substr($mask, 0, $pos));

			foreach ($matches as $match) {
				list(, $param, $name, $pattern, $class) = $match;  // $pattern is unsed

				if ($class !== '') {
					if (!isset(self::$styles[$class])) {
						throw new /*\*/InvalidStateException("Parameter '$name' has '$class' flag, but Route::\$styles['$class'] is not set.");
					}
					$meta = self::$styles[$class];

				} elseif (isset(self::$styles['?' . $name])) {
					$meta = self::$styles['?' . $name];

				} else {
					$meta = self::$styles['?#'];
				}

				if (isset($metadata[$name])) {
					$meta = $meta + $metadata[$name];
				}

				if (array_key_exists('default', $meta)) {
					$meta['fixity'] = self::OPTIONAL;
				}

				unset($meta['pattern']);
				$meta['filterTable2'] = empty($meta[self::FILTER_TABLE]) ? NULL : array_flip($meta[self::FILTER_TABLE]);

				$metadata[$name] = $meta;
				if ($param !== '') {
					$this->xlat[$name] = $param;
				}
			}
		}


		// 2) PARSE URI-PATH PART OF MASK
		$parts = preg_split(
			'/<([^># ]+) *([^>#]*)(#?[^>]*)>/',  // <parameter-name [pattern][#class]>
			$mask,
			-1,
			PREG_SPLIT_DELIM_CAPTURE
		);

		$optional = TRUE;
		$sequence = array();
		$i = count($parts) - 1;
		$re = '';
		do {
			array_unshift($sequence, $parts[$i]);
			$re = preg_quote($parts[$i], '#') . $re;
			if ($i === 0) break;
			$i--;

			$class = $parts[$i]; $i--; // validation class
			$pattern = $parts[$i]; $i--; // validation condition (as regexp)
			$name = $parts[$i]; $i--; // parameter name
			array_unshift($sequence, $name);

			if ($name[0] === '?') { // "foo" parameter
				$re = '(?:' . $pattern . ')' . $re;
				$sequence[1] = substr($name, 1) . $sequence[1];
				continue;
			}

			// check name (limitation by regexp)
			if (preg_match('#[^a-z0-9_-]#i', $name)) {
				throw new /*\*/InvalidArgumentException("Parameter name must be alphanumeric string due to limitations of PCRE, '$name' is invalid.");
			}

			// pattern, condition & metadata
			if ($class !== '') {
				if (!isset(self::$styles[$class])) {
					throw new /*\*/InvalidStateException("Parameter '$name' has '$class' flag, but Route::\$styles['$class'] is not set.");
				}
				$meta = self::$styles[$class];

			} elseif (isset(self::$styles[$name])) {
				$meta = self::$styles[$name];

			} else {
				$meta = self::$styles['#'];
			}

			if (isset($metadata[$name])) {
				$meta = $meta + $metadata[$name];
			}

			if ($pattern == '' && isset($meta[self::PATTERN])) {
				$pattern = $meta[self::PATTERN];
			}

			$meta['filterTable2'] = empty($meta[self::FILTER_TABLE]) ? NULL : array_flip($meta[self::FILTER_TABLE]);
			if (isset($meta['default'])) {
				if (isset($meta['filterTable2'][$meta['default']])) {
					$meta['defOut'] = $meta['filterTable2'][$meta['default']];

				} elseif (isset($meta[self::FILTER_OUT])) {
					$meta['defOut'] = call_user_func($meta[self::FILTER_OUT], $meta['default']);

				} else {
					$meta['defOut'] = $meta['default'];
				}
			}
			$meta[self::PATTERN] = "#$pattern$#A" . ($this->flags & self::CASE_SENSITIVE ? '' : 'i');
			$metadata[$name] = $meta;

			// include in expression
			$tmp = str_replace('-', '___', $name); // dirty trick to enable '-' in parameter name
			if (isset($meta['fixity'])) { // has default value?
				if (!$optional) {
					throw new /*\*/InvalidArgumentException("Parameter '$name' must not be optional because parameters standing on the right side are not optional.");
				}
				$re = '(?:(?P<' . $tmp . '>' . $pattern . ')' . $re . ')?';
				$metadata[$name]['fixity'] = self::PATH_OPTIONAL;

			} else {
				$optional = FALSE;
				$re = '(?P<' . $tmp . '>' . $pattern . ')' . $re;
			}
		} while (TRUE);

		$this->re = '#' . $re . '/?$#A' . ($this->flags & self::CASE_SENSITIVE ? '' : 'i');
		$this->metadata = $metadata;
		$this->sequence = $sequence;
	}



	/********************* Utilities ****************d*g**/



	/**
	 * Proprietary cache aim.
	 * @return string|FALSE
	 */
	public function getTargetPresenter()
	{
		if ($this->flags & self::ONE_WAY) {
			return FALSE;
		}

		$m = $this->metadata;
		$presenter = '';

		if (isset($m[self::MODULE_KEY]['fixity'])) {
			if ($m[self::MODULE_KEY]['fixity'] !== self::CONSTANT) {
				return NULL;
			}
			$presenter = $m[self::MODULE_KEY]['default'] . ':';
		}

		if (isset($m[self::PRESENTER_KEY]['fixity'])) {
			if ($m[self::PRESENTER_KEY]['fixity'] === self::CONSTANT) {
				return $presenter . $m[self::PRESENTER_KEY]['default'];
			}
		}
		return NULL;
	}



	/**
	 * Rename keys in array.
	 * @param  array
	 * @param  array
	 * @return array
	 */
	private static function renameKeys($arr, $xlat)
	{
		if (empty($xlat)) return $arr;

		$res = array();
		$occupied = array_flip($xlat);
		foreach ($arr as $k => $v) {
			if (isset($xlat[$k])) {
				$res[$xlat[$k]] = $v;

			} elseif (!isset($occupied[$k])) {
				$res[$k] = $v;
			}
		}
		return $res;
	}



	/********************* Inflectors ****************d*g**/



	/**
	 * camelCaseView name -> dash-separated.
	 * @param  string
	 * @return string
	 */
	private static function view2path($s)
	{
		$s = preg_replace('#(.)(?=[A-Z])#', '$1-', $s);
		$s = strtolower($s);
		$s = rawurlencode($s);
		return $s;
	}



	/**
	 * dash-separated -> camelCaseView name.
	 * @param  string
	 * @return string
	 */
	private static function path2view($s)
	{
		$s = strtolower($s);
		$s = preg_replace('#-(?=[a-z])#', ' ', $s);
		$s = substr(ucwords('x' . $s), 1);
		//$s = lcfirst(ucwords($s));
		$s = str_replace(' ', '', $s);
		return $s;
	}



	/**
	 * PascalCase:Presenter name -> dash-and-dot-separated.
	 * @param  string
	 * @return string
	 */
	private static function presenter2path($s)
	{
		$s = strtr($s, ':', '.');
		$s = preg_replace('#([^.])(?=[A-Z])#', '$1-', $s);
		$s = strtolower($s);
		$s = rawurlencode($s);
		return $s;
	}



	/**
	 * dash-and-dot-separated -> PascalCase:Presenter name.
	 * @param  string
	 * @return string
	 */
	private static function path2presenter($s)
	{
		$s = strtolower($s);
		$s = preg_replace('#([.-])(?=[a-z])#', '$1 ', $s);
		$s = ucwords($s);
		$s = str_replace('. ', ':', $s);
		$s = str_replace('- ', '', $s);
		return $s;
	}



	/********************* Route::$styles manipulator ****************d*g**/



	/**
	 * Creates new style.
	 * @param  string  style name (#style, urlParameter, ?queryParameter)
	 * @param  string  optional parent style name
	 * @param  void
	 */
	public static function addStyle($style, $parent = '#')
	{
		if (isset(self::$styles[$style])) {
			throw new InvalidArgumentException("Style '$style' already exists.");
		}

		if ($parent !== NULL) {
			if (!isset(self::$styles[$parent])) {
				throw new InvalidArgumentException("Parent style '$parent' doesn't exist.");
			}
			self::$styles[$style] = self::$styles[$parent];

		} else {
			self::$styles[$style] = array();
		}
	}



	/**
	 * Changes style property value.
	 * @param  string  style name (#style, urlParameter, ?queryParameter)
	 * @param  string  property name (Route::PATTERN, Route::FILTER_IN, Route::FILTER_OUT, Route::FILTER_TABLE)
	 * @param  mixed   property value
	 * @param  void
	 */
	public static function setStyleProperty($style, $key, $value)
	{
		if (!isset(self::$styles[$style])) {
			throw new InvalidArgumentException("Style '$style' doesn't exist.");
		}
		self::$styles[$style][$key] = $value;
	}

}
