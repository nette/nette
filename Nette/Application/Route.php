<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Application
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
 * @version    $Revision$ $Date$
 */
class Route extends /*Nette::*/Object implements IRouter
{
	const PRESENTER_KEY = 'presenter';
	const MODULE_KEY = 'module';

	// flags
	const CASE_SENSITIVE = 2;

	// uri type
	const HOST = 1;
	const PATH = 2;
	const RELATIVE = 3;

	/** @var bool */
	public static $defaultCaseSensitivity = FALSE;

	/** @var array */
	public static $defaults = array(
		'' => array(
			're' => '[^/]+',
		),
		'#d' => array(
			're' => '[a-z][a-z0-9-]*',
			'filterIn' => /*Nette::Application::*/'Route::dash2camel',
			'filterOut' => /*Nette::Application::*/'Route::camel2dash',
		),
		'module' => array(
			're' => '[a-z][a-z0-9.-]*',
			'filterIn' => /*Nette::Application::*/'Route::dash2pascal',
			'filterOut' => /*Nette::Application::*/'Route::pascal2dash',
		),
		'presenter' => array(
			're' => '[a-z][a-z0-9.-]*',
			'filterIn' => /*Nette::Application::*/'Route::dash2pascal',
			'filterOut' => /*Nette::Application::*/'Route::pascal2dash',
		),
		'view' => array(
			're' => '[a-z][a-z0-9-]*',
			'filterIn' => /*Nette::Application::*/'Route::dash2camel',
			'filterOut' => /*Nette::Application::*/'Route::camel2dash',
		),
	);

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
	 */
	public function __construct($mask, array $defaults = array(), $flags = 0)
	{
		if (self::$defaultCaseSensitivity) {
			$flags = $flags | self::CASE_SENSITIVE;
		}
		$this->flags = $flags;
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

		if (!preg_match($this->re, rtrim($path, '/') . '/', $params)) {
			return NULL;
		}
		$params = array_diff_key($params, range(0, count($params))); // deletes numeric keys


		// 2) fixed
		$defaults = array();
		foreach ($this->metadata as $name => $meta) {
			if (isset($params[$name])) {
				if (isset($meta['filterIn'])) {
					$params[$name] = call_user_func($meta['filterIn'], $params[$name]);
				}
			} elseif (isset($meta['fixed'])) {
				if ($meta['fixed'] !== 0) { // force now
					$params[$name] = $meta['default'];
				} else { // append later
					$defaults[$name] = $meta['default'];
				}
			}
		}


		// 3) query
		if ($this->xlat) {
			$params += self::renameKeys((array) $context->getQuery(), array_flip($this->xlat));
		} else {
			$params += (array) $context->getQuery();
		}
		//$params += (array) $context->getPost();


		// 4) defaults
		$params += $defaults;


		// build PresenterRequest
		if (isset($this->metadata[self::MODULE_KEY])) {
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

		$params = (array) $request->getParams();
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
			if (isset($params[$name]) && isset($meta['fixed'])) {
				if ($params[$name] == $meta['default']) {  // intentionally ==
					// remove default values; NULL values are retain
					unset($params[$name]);

				} elseif ($meta['fixed'] === 2) {
					return NULL; // missing or wrong parameter '$name'
				}
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

			$cond = $sequence[$i]; $i--; // validation condition (as regexp); unused
			$name = $sequence[$i]; $i--; // parameter name
			$meta = isset($metadata[$name]) ? $metadata[$name] : NULL;

			if (isset($params[$name])) {
				$optional = FALSE;
				if (isset($meta['filterOut'])) {
					$uri = call_user_func($meta['filterOut'], $params[$name]) . $uri;
				} else {
					$uri = $params[$name] . $uri;
				}
				unset($params[$name]);

			} elseif (isset($meta['fixed'])) { // has default value?
				if ($optional) {
					$uri = '';
				} else {
					if (isset($meta['filterOut'])) {
						$uri = call_user_func($meta['filterOut'], $meta['default']) . $uri;
					} else {
						$uri = $meta['default'] . $uri;
					}
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
				list(, $param, $name, $cond) = $match;  // $cond is unsed
				$metadata[$name]['fixed'] = 0;
				if ($param !== '') {
					$this->xlat[$name] = $param;
				}
			}
		}


		// parse request uri part of mask
		$this->sequence = $sequence = preg_split(
			'#<([^> ]+) *([^>]*)>#',  // <parameter-name [validation-expr]>
			$mask,
			-1,
			PREG_SPLIT_DELIM_CAPTURE
		);

		$optional = TRUE;
		$i = count($sequence) - 1;
		$re = '';
		do {
			$re = preg_quote($sequence[$i], '#') . $re;
			if ($i === 0) break;
			$i--;

			$cond = $sequence[$i]; $i--; // validation condition (as regexp)
			$name = $sequence[$i]; $i--; // parameter name

			// check name (limitation by regexp)
			if (preg_match('#[^a-z0-9_]#i', $name)) {
				throw new /*::*/InvalidArgumentException("Invalid parameter name '$name'.");
			}

			// check cond
			$defMeta = NULL;
			if ($cond === '') {
				$defMeta = self::$defaults[isset(self::$defaults[$name]) ? $name : ''];

			} elseif ($cond[0] === '#') {
				if (!isset(self::$defaults[$cond])) {
					throw new /*::*/InvalidStateException("Missing default metadata key '$cond'.");
				}
				$defMeta = self::$defaults[$cond];
			}

			if ($defMeta !== NULL) {
				if (isset($metadata[$name])) {
					$metadata[$name] = $defMeta + $metadata[$name];
				} else {
					$metadata[$name] = $defMeta;
				}
				if (isset($metadata[$name]['re'])) {
					$cond = $metadata[$name]['re'];
				}
			}

			// include in expression
			if (isset($metadata[$name]['fixed'])) { // has default value?
				if (!$optional) {
					throw new /*::*/InvalidArgumentException("Parameter '$name' must not be optional because parameters to the right are optional.");
				}
				$re = '(?:(?P<' . $name . '>' . $cond . ')' . $re . ')?';
				$metadata[$name]['fixed'] = 1;

			} else {
				$optional = FALSE;
				$re = '(?P<' . $name . '>' . $cond . ')' . $re;
			}
		} while (TRUE);

		$this->re = '#' . $re . '/?$#A' . ($this->flags & self::CASE_SENSITIVE ? '' : 'i');
		$this->metadata = $metadata;
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
	 * camelCase -> dash-separated.
	 * @param  string
	 * @return string
	 */
	private static function camel2dash($s)
	{
		$s = preg_replace('#(.)(?=[A-Z])#', '$1-', $s);
		$s = strtolower($s);
		return $s;
	}



	/**
	 * dash-separated -> camelCase.
	 * @param  string
	 * @return string
	 */
	private static function dash2camel($s)
	{
		$s = strtolower($s);
		$s = preg_replace('#-(?=[a-z])#', ' ', $s);
		$s = ucwords('x' . $s);
		$s = substr($s, 1);
		$s = str_replace(' ', '', $s);
		return $s;
	}



	/**
	 * PascalCase:WithColons -> dash-and-dot-separated.
	 * @param  string
	 * @return string
	 */
	private static function pascal2dash($s)
	{
		$s = strtr($s, ':', '.');
		$s = preg_replace('#([^.])(?=[A-Z])#', '$1-', $s);
		$s = strtolower($s);
		return $s;
	}



	/**
	 * dash-and-dot-separated -> PascalCase:WithColons.
	 * @param  string
	 * @return string
	 */
	private static function dash2pascal($s)
	{
		$s = strtolower($s);
		$s = preg_replace('#([.-])(?=[a-z])#', '$1 ', $s);
		$s = ucwords($s);
		$s = str_replace('. ', ':', $s);
		$s = str_replace('- ', '', $s);
		return $s;
	}

}
