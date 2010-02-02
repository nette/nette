<?php
/**
 * Nette Framework Requirements Checker.
 *
 * This script will check if your system meets the requirements for running Nette Framework.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
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
define('CHECKER_VERSION', '1.4');

$reflection = class_exists('ReflectionFunction') && !iniFlag('zend.ze1_compatibility_mode') ? new ReflectionFunction('paint') : NULL;

paint(array(
	array(
		'title' => 'Web server',
		'message' => $_SERVER['SERVER_SOFTWARE'],
	),

	array(
		'title' => 'PHP version',
		'required' => TRUE,
		'passed' => version_compare(PHP_VERSION, '5.2.0', '>='),
		'message' => PHP_VERSION,
		'description' => 'Your PHP version is too old. Nette Framework requires at least PHP 5.2.0 or higher.',
	),

	array(
		'title' => 'Memory limit',
		'message' => ini_get('memory_limit'),
	),

	'ha' => array(
		'title' => '.htaccess file protection',
		'required' => FALSE,
		'description' => 'File protection by <code>.htaccess</code> is optional. If it is absent, you must be careful to put files into document_root folder.',
		'script' => "var el = document.getElementById('resha');\nel.className = typeof checkerScript == 'undefined' ? 'passed' : 'warning';\nel.parentNode.removeChild(el.nextSibling.nodeType === 1 ? el.nextSibling : el.nextSibling.nextSibling);",
	),

	array(
		'title' => 'Function ini_set',
		'required' => FALSE,
		'passed' => function_exists('ini_set'),
		'description' => 'Function <code>ini_set()</code> is disabled. Some parts of Nette Framework may not work properly.',
	),

	array(
		'title' => 'Magic quotes',
		'required' => FALSE,
		'passed' => !iniFlag('magic_quotes_gpc') && !iniFlag('magic_quotes_runtime'),
		'message' => 'Disabled',
		'errorMessage' => 'Enabled',
		'description' => 'Magic quotes <code>magic_quotes_gpc</code> and <code>magic_quotes_runtime</code> are enabled and should be turned off. Nette Framework disables <code>magic_quotes_runtime</code> automatically.',
	),

	array(
		'title' => 'Register_globals',
		'required' => TRUE,
		'passed' => !iniFlag('register_globals'),
		'message' => 'Disabled',
		'errorMessage' => 'Enabled',
		'description' => 'Configuration directive <code>register_globals</code> is enabled. Nette Framework requires this to be disabled.',
	),

	array(
		'title' => 'Zend.ze1_compatibility_mode',
		'required' => TRUE,
		'passed' => !iniFlag('zend.ze1_compatibility_mode'),
		'message' => 'Disabled',
		'errorMessage' => 'Enabled',
		'description' => 'Configuration directive <code>zend.ze1_compatibility_mode</code> is enabled. Nette Framework requires this to be disabled.',
	),

	array(
		'title' => 'Variables_order',
		'required' => TRUE,
		'passed' => strpos(ini_get('variables_order'), 'G') !== FALSE && strpos(ini_get('variables_order'), 'P') !== FALSE && strpos(ini_get('variables_order'), 'C') !== FALSE,
		'description' => 'Configuration directive <code>variables_order</code> is missing. Nette Framework requires this to be set.',
	),

	array(
		'title' => 'Reflection extension',
		'required' => TRUE,
		'passed' => (bool) $reflection,
		'description' => 'Reflection extension is required.',
	),

	array(
		'title' => 'Reflection phpDoc',
		'required' => FALSE,
		'passed' => $reflection ? strpos($reflection->getDocComment(), 'Paints') !== FALSE : FALSE,
		'description' => 'Reflection phpDoc are not available (probably due to an eAccelerator bug). Persistent parameters must be declared using static function.',
	),

	array(
		'title' => 'SPL extension',
		'required' => TRUE,
		'passed' => extension_loaded('SPL'),
		'description' => 'SPL extension is required.',
	),

	array(
		'title' => 'PCRE extension',
		'required' => TRUE,
		'passed' => extension_loaded('pcre'),
		'description' => 'PCRE extension is required.',
	),

	array(
		'title' => 'ICONV extension',
		'required' => TRUE,
		'passed' => extension_loaded('iconv') && (ICONV_IMPL !== 'unknown') && @iconv('UTF-16', 'UTF-8//IGNORE', iconv('UTF-8', 'UTF-16//IGNORE', 'test')) === 'test',
		'message' => 'Enabled and works properly',
		'errorMessage' => 'Disabled or works not properly',
		'description' => 'ICONV extension is required and must work properly.',
	),

	array(
		'title' => 'Multibyte String extension',
		'required' => FALSE,
		'passed' => extension_loaded('mbstring'),
		'description' => 'Multibyte String extension is absent. Some internationalization components may not work properly.',
	),

	array(
		'title' => 'PHP tokenizer',
		'required' => TRUE,
		'passed' => extension_loaded('tokenizer'),
		'description' => 'PHP tokenizer is required.',
	),

	array(
		'title' => 'Multibyte String function overloading',
		'required' => TRUE,
		'passed' => !extension_loaded('mbstring') || !(mb_get_info('func_overload') & 2),
		'message' => 'Disabled',
		'errorMessage' => 'Enabled',
		'description' => 'Multibyte String function overloading is enabled. Nette Framework requires this to be disabled. If it is enabled, some string function may not work properly.',
	),

	array(
		'title' => 'SQLite extension',
		'required' => FALSE,
		'passed' => extension_loaded('sqlite'),
		'description' => 'SQLite extension is absent. You will not be able to use tags and priorities with <code>Nette\Caching\FileStorage</code>.',
	),

	array(
		'title' => 'Memcache extension',
		'required' => FALSE,
		'passed' => extension_loaded('memcache'),
		'description' => 'Memcache extension is absent. You will not be able to use <code>Nette\Caching\MemcachedStorage</code>.',
	),

	array(
		'title' => 'GD extension',
		'required' => FALSE,
		'passed' => extension_loaded('gd'),
		'description' => 'GD extension is absent. You will not be able to use <code>Nette\Image</code>.',
	),

	array(
		'title' => 'Bundled GD extension',
		'required' => FALSE,
		'passed' => extension_loaded('gd') && GD_BUNDLED,
		'description' => 'Bundled GD extension is absent. You will not be able to use some function as <code>Nette\Image::filter()</code> or <code>Nette\Image::rotate()</code>.',
	),

	array(
		'title' => 'ImageMagick library',
		'required' => FALSE,
		'passed' => @exec('identify -format "%w,%h,%m" ' . addcslashes(dirname(__FILE__) . '/assets/logo.gif', ' ')) === '176,104,GIF', // intentionally @
		'description' => 'ImageMagick server library is absent. You will not be able to use <code>Nette\ImageMagick</code>.',
	),

	array(
		'title' => 'Fileinfo extension or mime_content_type()',
		'required' => FALSE,
		'passed' => extension_loaded('fileinfo') || function_exists('mime_content_type'),
		'description' => 'Fileinfo extension or function <code>mime_content_type()</code> are absent. You will not be able to determine mime type of uploaded files.',
	),

	/**/array(
		'title' => 'HTTP extension',
		'required' => FALSE,
		'passed' => !extension_loaded('http'),
		'message' => 'Disabled',
		'errorMessage' => 'Enabled',
		'description' => 'HTTP extension has naming conflict with Nette Framework. You have to disable this extension or use „prefixed“ version.',
	),
	/**/
	array(
		'title' => 'HTTP_HOST or SERVER_NAME',
		'required' => TRUE,
		'passed' => isset($_SERVER["HTTP_HOST"]) || isset($_SERVER["SERVER_NAME"]),
		'message' => 'Present',
		'errorMessage' => 'Absent',
		'description' => 'Either <code>$_SERVER["HTTP_HOST"]</code> or <code>$_SERVER["SERVER_NAME"]</code> must be available for resolving host name.',
	),

	array(
		'title' => 'REQUEST_URI or ORIG_PATH_INFO',
		'required' => TRUE,
		'passed' => isset($_SERVER["REQUEST_URI"]) || isset($_SERVER["ORIG_PATH_INFO"]),
		'message' => 'Present',
		'errorMessage' => 'Absent',
		'description' => 'Either <code>$_SERVER["REQUEST_URI"]</code> or <code>$_SERVER["ORIG_PATH_INFO"]</code> must be available for resolving request URL.',
	),

	array(
		'title' => 'SCRIPT_FILENAME, SCRIPT_NAME, PHP_SELF',
		'required' => TRUE,
		'passed' => isset($_SERVER["SCRIPT_FILENAME"], $_SERVER["SCRIPT_NAME"], $_SERVER["PHP_SELF"]),
		'message' => 'Present',
		'errorMessage' => 'Absent',
		'description' => '<code>$_SERVER["SCRIPT_FILENAME"]</code> and <code>$_SERVER["SCRIPT_NAME"]</code> and <code>$_SERVER["PHP_SELF"]</code> must be available for resolving script file path.',
	),

	array(
		'title' => 'SERVER_ADDR or LOCAL_ADDR',
		'required' => TRUE,
		'passed' => isset($_SERVER["SERVER_ADDR"]) || isset($_SERVER["LOCAL_ADDR"]),
		'message' => 'Present',
		'errorMessage' => 'Absent',
		'description' => '<code>$_SERVER["SERVER_ADDR"]</code> or <code>$_SERVER["LOCAL_ADDR"]</code> must be available for detecting development / production mode.',
	),
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

	$errors = $warnings = FALSE;

	foreach ($requirements as $id => $requirement)
	{
		$requirements[$id] = $requirement = (object) $requirement;
		if (isset($requirement->passed) && !$requirement->passed) {
			if ($requirement->required) {
				$errors = TRUE;
			} else {
				$warnings = TRUE;
			}
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
