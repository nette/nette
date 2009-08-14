<?php
/**
 * Nette Framework Requirements Checker.
 *
 * This script will check if your system meets the requirements for running Nette Framework.
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 */



/**
 * Check PHP configuration.
 */
foreach (array('function_exists', 'version_compare', 'extension_loaded', 'ini_get') as $function) {
	if (!function_exists($function)) {
		die("Error: function '$function' is required by Nette Framework and this Requirements Checker.");
	}
}



/**
 * Check Nette Framework requirements.
 */
define('CHECKER_VERSION', '1.1');
define('REQUIRED', TRUE);
define('OPTIONAL', FALSE);

$reflection = class_exists('ReflectionFunction') && !iniFlag('zend.ze1_compatibility_mode') ? new ReflectionFunction('paint') : NULL;

paint(array(
	array(
		'PHP version',
		REQUIRED,
		version_compare(PHP_VERSION, '5.2.0', '>='),
		'PHP version 5.2.0 or higher is required by Nette Framework.',
	),
	array(
		'Function ini_set',
		OPTIONAL,
		function_exists('ini_set'),
		'Function ini_set() is optional. If it is absent, some parts of framework may not work properly.',
	),
	array(
		'Reflection extension',
		REQUIRED,
		(bool) $reflection,
		'Reflection extension is required.',
	),
	array(
		'Reflection phpDoc',
		OPTIONAL,
		$reflection ? strpos($reflection->getDocComment(), 'Paints') !== FALSE : FALSE,
		'Reflection phpDoc is optional. If it is absent, persistent parameters must be declared using static function.',
	),
	array(
		'SPL extension',
		REQUIRED,
		extension_loaded('SPL'),
		'SPL extension is required.',
	),
	array(
		'PCRE extension',
		REQUIRED,
		extension_loaded('pcre'),
		'PCRE extension is required.',
	),
	array(
		'ICONV extension',
		REQUIRED,
		extension_loaded('iconv') && (ICONV_IMPL !== 'unknown') && @iconv('UTF-16', 'UTF-8//IGNORE', iconv('UTF-8', 'UTF-16//IGNORE', 'test')) === 'test',
		'ICONV extension is required and must work properly.',
	),
	array(
		'$_SERVER["HTTP_HOST"] or "SERVER_NAME"',
		REQUIRED,
		isset($_SERVER["HTTP_HOST"]) || isset($_SERVER["SERVER_NAME"]),
		'Either $_SERVER["HTTP_HOST"] or $_SERVER["SERVER_NAME"] must be available for resolving host name.',
	),
	array(
		'$_SERVER["REQUEST_URI"] or "ORIG_PATH_INFO"',
		REQUIRED,
		isset($_SERVER["REQUEST_URI"]) || isset($_SERVER["ORIG_PATH_INFO"]),
		'Either $_SERVER["REQUEST_URI"] or $_SERVER["ORIG_PATH_INFO"] must be available for resolving request URL.',
	),
	array(
		'$_SERVER["SCRIPT_FILENAME"] and "SCRIPT_NAME" and "PHP_SELF"',
		REQUIRED,
		isset($_SERVER["SCRIPT_FILENAME"], $_SERVER["SCRIPT_NAME"], $_SERVER["PHP_SELF"]),
		'$_SERVER["SCRIPT_FILENAME"] and $_SERVER["SCRIPT_NAME"] and $_SERVER["PHP_SELF"] must be available for resolving script file path.',
	),
	array(
		'$_SERVER["SERVER_ADDR"] or "LOCAL_ADDR"',
		REQUIRED,
		isset($_SERVER["SERVER_ADDR"]) || isset($_SERVER["LOCAL_ADDR"]),
		'$_SERVER["SERVER_ADDR"] or $_SERVER["LOCAL_ADDR"] must be available for detecting development/production mode.',
	),
	'ha' => array(
		'.htaccess file protection',
		OPTIONAL,
		NULL,
		'File protection by .htaccess is optional. If it is absent, you must be careful to put files into document_root folder.',
		'<script>var el = document.getElementById("resha").getElementsByTagName("td").item(0); el.className = el.innerHTML = typeof checkerScript == "undefined" ? "passed" : "warning";</script>',
	),
	array(
		'Multibyte String extension',
		OPTIONAL,
		extension_loaded('mbstring'),
		'Multibyte String extension is optional. If it is absent, some internationalization components may not work properly.',
	),
	array(
		'Multibyte String function overloading off',
		REQUIRED,
		!extension_loaded('mbstring') || !(mb_get_info('func_overload') & 2),
		'Multibyte String function overloading must be turned off. If it is enabled, some string function may not work properly.',
	),
	array(
		'Magic quotes off',
		OPTIONAL,
		!iniFlag('magic_quotes_gpc') && !iniFlag('magic_quotes_runtime'),
		'Magic quotes "magic_quotes_gpc" & "magic_quotes_runtime" should be turned off. Framework disables magic_quotes_runtime automatically.',
	),
	array(
		'Register_globals off',
		OPTIONAL,
		!iniFlag('register_globals'),
		'Register_globals should be turned off.',
	),
	array(
		'Zend.ze1_compatibility_mode off',
		REQUIRED,
		!iniFlag('zend.ze1_compatibility_mode'),
		'zend.ze1_compatibility_mode must be turned off.',
	),
	array(
		'Variables_order',
		REQUIRED,
		strpos(ini_get('variables_order'), 'G') !== FALSE && strpos(ini_get('variables_order'), 'P') !== FALSE && strpos(ini_get('variables_order'), 'C') !== FALSE,
		'Variables_order is required.',
	),
	array(
		'Memcache extension',
		OPTIONAL,
		extension_loaded('memcache'),
		'Memcache extension is optional. If it is absent, you will not be able to use Nette\Caching\MemcachedStorage.',
	),
	array(
		'GD extension',
		OPTIONAL,
		extension_loaded('gd'),
		'GD extension is optional. If it is absent, you will not be able to use Nette\Image.',
	),
	array(
		'Bundled GD extension',
		OPTIONAL,
		extension_loaded('gd') && GD_BUNDLED,
		'Bundled GD extension is optional. If it is absent, you will not be able to use some function as Nette\Image::filter() or Nette\Image::rotate().',
	),
	array(
		'ImageMagick library',
		OPTIONAL,
		@exec('identify -format "%w,%h,%m" ' . addcslashes(dirname(__FILE__) . '/assets/checker.gif', ' ')) === '176,104,GIF', // intentionally @
		'ImageMagick server library is optional. If it is absent, you will not be able to use Nette\ImageMagick.',
	),
	array(
		'Fileinfo extension or function mime_content_type',
		OPTIONAL,
		extension_loaded('fileinfo') || function_exists('mime_content_type'),
		'Fileinfo extension or function mime_content_type are optional. If they are absent, you will not be able to determine mime type of uploaded files.',
	),
	/**/array(
		'Disabled HTTP extension',
		OPTIONAL,
		!extension_loaded('http'),
		'HTTP extension has naming conflict with Nette Framework. If it is present, you will have to use Prefixed version.',
	),/**/
));




/**
 * Paints checker.
 * @param  array
 * @return void
 */
function paint($requirements)
{
	$redirect = round(time(), -1);
	if (!isset($_GET) || (isset($_GET['r']) && $_GET['r'] == $redirect)) {
		$redirect = NULL;
	}

	$errors = array(
		REQUIRED => 0,
		OPTIONAL => 0,
	);

	foreach ($requirements as $requirement)
	{
		list(, $required, $result) = $requirement;
		if (isset($result) && !$result) {
			$errors[$required]++;
		}
	}

	require dirname(__FILE__) . '/assets/checker.phtml';
}



/**
 * Gets the boolean value of a configuration option.
 * @param  string  configuration option name
 * @return bool
 */
function iniFlag($var)
{
	$status = strtolower(ini_get($var));
	return $status === 'on' || $status === 'true' || $status === 'yes' || $status % 256;
}
