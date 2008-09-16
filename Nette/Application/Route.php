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
 * @package    Nette::Application
 * @version    $Id$
 */

/*namespace Nette::Application;*/



require_once dirname(__FILE__) . '/../Object.php';

require_once dirname(__FILE__) . '/../Application/IRouter.php';



/**
 * The bidirectional route is responsible for mapping.
 * HTTP request to a PresenterRoute object for dispatch and vice-versa.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 */
class Route extends /*Nette::*/Object implements IRouter
{
	const PRESENTER_KEY = 'presenter';
	const MODULE_KEY = 'module';

	// flags
	const CASE_SENSITIVE = 256;

	// uri type
	const HOST = 1;
	const PATH = 2;
	const RELATIVE = 3;

	/** @var bool */
	public static $defaultFlags = 0;

	/** @var array */
	public static $styles = array(
		'#' => array( // default style for path parameters
			'pattern' => '[^/]+',
			'filterIn' => 'rawurldecode',
			'filterOut' => 'rawurlencode',
		),
		'?#' => array( // default style for query parameters
		),
		'module' => array(
			'pattern' => '[a-z][a-z0-9.-]*',
			'filterIn' => /*Nette::Application::*/'Route::path2presenter',
			'filterOut' => /*Nette::Application::*/'Route::presenter2path',
		),
		'presenter' => array(
			'pattern' => '[a-z][a-z0-9.-]*',
			'filterIn' => /*Nette::Application::*/'Route::path2presenter',
			'filterOut' => /*Nette::Application::*/'Route::presenter2path',
		),
		'view' => array(
			'pattern' => '[a-z][a-z0-9-]*',
			'filterIn' => /*Nette::Application::*/'Route::path2view',
			'filterOut' => /*Nette::Application::*/'Route::view2path',
		),
		'?module' => array(
			'filterOut' => 'strtolower',
		),
		'?presenter' => array(
			'filterOut' => 'strtolower',
		),
		'?view' => array(
			'filterOut' => 'strtolower',
		),
	);

	/** @var string */
	public $mask;

	/** @var array */
	private $sequence;

	/** @var string  regular expression pattern */
	private $re;

	/** @var array of [default & fixed, filterIn, filterOut] */
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
	 * @param  Nette::Web::IHttpRequest
	 * @return PresenterRequest|NULL
	 */
	public function match(/*Nette::Web::*/IHttpRequest $context)
	{
		// combine with precedence: mask (params in URL-path), fixed, query, (post,) defaults

		// 1) mask
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

		if (!preg_match($this->re, $path, $params)) {
			return NULL;
		}
		$params = array_diff_key($params, range(0, count($params))); // deletes numeric keys


		// 2) fixed
		$defaults = array();
		foreach ($this->metadata as $name => $meta) {
			if (isset($params[$name])) {
				if (isset($meta['filterIn'])) { // applyies filterIn only to path parameters
					$params[$name] = call_user_func($meta['filterIn'], $params[$name]);
				}
			} elseif (isset($meta['fixed'])) {
				if ($meta['fixed'] !== 0) { // force now
					$params[$name] = $meta['default'];
				} else { // append later - after query params
					$defaults[$name] = $meta['default'];
				}
			}
		}


		// 3) query
		if ($this->xlat) {
			$params += self::renameKeys($context->getQuery(), array_flip($this->xlat));
		} else {
			$params += $context->getQuery();
		}
		//$params += $context->getPost();


		// 4) defaults
		$params += $defaults;


		// build PresenterRequest
		if (!isset($params[self::PRESENTER_KEY])) {
			throw new /*::*/InvalidStateException('Missing presenter in route definition.');
		}
		if (isset($this->metadata[self::MODULE_KEY])) {
			if (!isset($params[self::MODULE_KEY])) {
				throw new /*::*/InvalidStateException('Missing module in route definition.');
			}
			$presenter = $params[self::MODULE_KEY] . ':' . $params[self::PRESENTER_KEY];
			unset($params[self::MODULE_KEY], $params[self::PRESENTER_KEY]);
		} else {
			$presenter = $params[self::PRESENTER_KEY];
			unset($params[self::PRESENTER_KEY]);
		}

		return new PresenterRequest(
			$presenter,
			$context->getMethod() === 'POST' ? PresenterRequest::HTTP_POST : PresenterRequest::HTTP_GET,
			$params,
			$context->getPost(),
			$context->getFiles()
		);
	}



	/**
	 * Constructs URL path from PresenterRequest object.
	 * @param  Nette::Web::IHttpRequest
	 * @param  PresenterRequest
	 * @return string|NULL
	 */
	public function constructUrl(PresenterRequest $request, /*Nette::Web::*/IHttpRequest $context)
	{
		if ($this->flags & self::ONE_WAY) {
			return NULL;
		}

		$params = $request->getParams();
		$metadata = $this->metadata;

		$presenter = $request->getPresenterName();
		if (isset($metadata[self::MODULE_KEY])) {
			$a = strrpos($presenter, ':');
			$params[self::MODULE_KEY] = substr($presenter, 0, $a);
			$params[self::PRESENTER_KEY] = substr($presenter, $a + 1);
		} else {
			$params[self::PRESENTER_KEY] = $presenter;
		}

		foreach ($metadata as $name => $meta) {
			if (!isset($params[$name])) continue; // retains NULL values

			if (isset($meta['fixed'])) {
				if (strcasecmp($params[$name], $meta['default']) === 0) {  // intentionally ==
					// remove default values; NULL values are retain
					unset($params[$name]);
					continue;

				} elseif ($meta['fixed'] === 2) {
					return NULL; // missing or wrong parameter '$name'
				}
			}

			if (isset($meta['filterOut'])) {
				$params[$name] = call_user_func($meta['filterOut'], $params[$name]);
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

			if (isset($params[$name]) && $params[$name] != '') { // intentionally ==
				$optional = FALSE;
				$uri = $params[$name] . $uri;
				unset($params[$name]);

			} elseif (isset($metadata[$name]['fixed'])) { // has default value?
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
				'fixed' => 2, // 2=fully fixed, 1=must be in path, 0=not fixed
			);
		}

		// parse query part of mask
		$this->xlat = array();
		$pos = strpos($mask, ' ? ');
		if ($pos !== FALSE) {
			preg_match_all(
				'#(?:([a-zA-Z0-9_.-]+)=)?<([^> ]+) *([^>]*)>#',
				substr($mask, $pos + 1),
				$matches,
				PREG_SET_ORDER
			);
			$mask = rtrim(substr($mask, 0, $pos));

			foreach ($matches as $match) {
				list(, $param, $name, $pattern) = $match;  // $pattern is unsed
				if (isset(self::$styles['?' . $name])) {
					$meta = self::$styles['?' . $name];
				} else {
					$meta = self::$styles['?#'];
				}
				if (isset($metadata[$name])) {
					$meta = $meta + $metadata[$name];
				}
				$meta['fixed'] = 0;
				$metadata[$name] = $meta;
				if ($param !== '') {
					$this->xlat[$name] = $param;
				}
			}
		}


		// parse request uri part of mask
		$parts = preg_split(
			'/<([^># ]+) *([^>#]*)(#?[^>]*)>/',  // <parameter-name [validation-expr]>
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

			// check name (limitation by regexp)
			if (preg_match('#[^a-z0-9_]#i', $name)) {
				throw new /*::*/InvalidArgumentException("Parameter name must be alphanumeric string due to limitations of PCRE, '$name' is invalid.");
			}

			// pattern, condition & metadata
			if ($class !== '') {
				if (!isset(self::$styles[$class])) {
					throw new /*::*/InvalidStateException("Parameter '$name' has '$class' flag, but Route::\$styles['$class'] is not set.");
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

			if ($pattern == '' && isset($meta['pattern'])) {
				$pattern = $meta['pattern'];
			}

			if (isset($meta['default'])) {
				$meta['defOut'] = isset($meta['filterOut']) ? call_user_func($meta['filterOut'], $meta['default']) : $meta['default'];
			}
			$meta['pattern'] = $pattern;
			$metadata[$name] = $meta;

			// include in expression
			if (isset($meta['fixed'])) { // has default value?
				if (!$optional) {
					throw new /*::*/InvalidArgumentException("Parameter '$name' must not be optional because parameters standing on the right are not optional.");
				}
				$re = '(?:(?P<' . $name . '>' . $pattern . ')' . $re . ')?';
				$metadata[$name]['fixed'] = 1;

			} else {
				$optional = FALSE;
				$re = '(?P<' . $name . '>' . $pattern . ')' . $re;
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

		if (isset($m[self::MODULE_KEY]['fixed'])) {
			if ($m[self::MODULE_KEY]['fixed'] !== 2) {
				return NULL;
			}
			$presenter = $m[self::MODULE_KEY]['default'] . ':';
		}

		if (isset($m[self::PRESENTER_KEY]['fixed'])) {
			if ($m[self::PRESENTER_KEY]['fixed'] === 2) {
				return $presenter . $m[self::PRESENTER_KEY]['default'];
			}
		}
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
		foreach ($arr as $k => $v) {
			if (isset($xlat[$k])) {
				$res[$xlat[$k]] = $v;
			} else {
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

}
