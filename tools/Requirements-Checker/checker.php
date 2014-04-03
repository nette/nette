<?php

/**
 * Requirements Checker: This script will check if your system meets
 * the requirements for running Nette Framework.
 *
 * This file is part of the Nette Framework (http://nette.org)
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
 * Check assets folder, template file must be readable
 */
define('TEMPLATE_FILE', dirname(__FILE__) . '/assets/checker.phtml');
if (!is_readable(TEMPLATE_FILE)) {
	die('Error: template file is not readable. Check assets folder (part of distribution), it should be present, readable and contain readable template file.');
}



/**
 * Check Nette Framework requirements.
 */
$tests[] = array(
	'title' => 'Web server',
	'message' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown',
);

$tests[] = array(
	'title' => 'PHP version',
	'required' => TRUE,
	'passed' => version_compare(PHP_VERSION, '5.3.1', '>='),
	'message' => PHP_VERSION,
	'description' => 'Your PHP version is too old. Nette Framework requires at least PHP 5.3.1 or higher.',
);

$tests[] = array(
	'title' => 'Memory limit',
	'message' => ini_get('memory_limit'),
);

$tests['hf'] = array(
	'title' => '.htaccess file protection',
	'required' => FALSE,
	'description' => 'File protection by <code>.htaccess</code> is not present. You must be careful to put files into document_root folder.',
	'script' => '<script src="assets/denied/checker.js"></script> <script>displayResult("hf", typeof fileProtectionChecker == "undefined")</script>',
);

$tests['hr'] = array(
	'title' => '.htaccess mod_rewrite',
	'required' => FALSE,
	'description' => 'Mod_rewrite is probably not present. You will not be able to use Cool URL.',
	'script' => '<script src="assets/rewrite/checker"></script> <script>displayResult("hr", typeof modRewriteChecker == "boolean")</script>',
);

$tests[] = array(
	'title' => 'Function ini_set()',
	'required' => FALSE,
	'passed' => function_exists('ini_set'),
	'description' => 'Function <code>ini_set()</code> is disabled. Some parts of Nette Framework may not work properly.',
);

$tests[] = array(
	'title' => 'Function error_reporting()',
	'required' => TRUE,
	'passed' => function_exists('error_reporting'),
	'description' => 'Function <code>error_reporting()</code> is disabled. Nette Framework requires this to be enabled.',
);

$tests[] = array(
	'title' => 'Function flock()',
	'required' => TRUE,
	'passed' => flock(fopen(__FILE__, 'r'), LOCK_SH),
	'description' => 'Function <code>flock()</code> is not supported on this filesystem. Nette Framework requires this to process atomic file operations.',
);

$tests[] = array(
	'title' => 'Register_globals',
	'required' => TRUE,
	'passed' => !iniFlag('register_globals'),
	'message' => 'Disabled',
	'errorMessage' => 'Enabled',
	'description' => 'Configuration directive <code>register_globals</code> is enabled. Nette Framework requires this to be disabled.',
);

$tests[] = array(
	'title' => 'Variables_order',
	'required' => TRUE,
	'passed' => strpos(ini_get('variables_order'), 'G') !== FALSE && strpos(ini_get('variables_order'), 'P') !== FALSE && strpos(ini_get('variables_order'), 'C') !== FALSE,
	'description' => 'Configuration directive <code>variables_order</code> is missing. Nette Framework requires this to be set.',
);

$tests[] = array(
	'title' => 'Session auto-start',
	'required' => FALSE,
	'passed' => session_id() === '' && !defined('SID'),
	'description' => 'Session auto-start is enabled. Nette Framework recommends not to use this directive for security reasons.',
);

$tests[] = array(
	'title' => 'PCRE with UTF-8 support',
	'required' => TRUE,
	'passed' => @preg_match('/pcre/u', 'pcre'),
	'description' => 'PCRE extension must support UTF-8.',
);

$reflection = new ReflectionFunction('paint');
$tests[] = array(
	'title' => 'Reflection phpDoc',
	'required' => TRUE,
	'passed' => strpos($reflection->getDocComment(), 'Paints') !== FALSE,
	'description' => 'Reflection phpDoc are not available (probably due to an eAccelerator bug). You cannot use @annotations.',
);

$tests[] = array(
	'title' => 'ICONV extension',
	'required' => TRUE,
	'passed' => extension_loaded('iconv') && (ICONV_IMPL !== 'unknown') && @iconv('UTF-16', 'UTF-8//IGNORE', iconv('UTF-8', 'UTF-16//IGNORE', 'test')) === 'test',
	'message' => 'Enabled and works properly',
	'errorMessage' => 'Disabled or does not work properly',
	'description' => 'ICONV extension is required and must work properly.',
);

$tests[] = array(
	'title' => 'JSON extension',
	'required' => TRUE,
	'passed' => extension_loaded('json'),
);

$tests[] = array(
	'title' => 'Fileinfo extension',
	'required' => FALSE,
	'passed' => extension_loaded('fileinfo'),
	'description' => 'Fileinfo extension is absent. You will not be able to detect content-type of uploaded files.',
);

$tests[] = array(
	'title' => 'PHP tokenizer',
	'required' => TRUE,
	'passed' => extension_loaded('tokenizer'),
	'description' => 'PHP tokenizer is required.',
);

$tests[] = array(
	'title' => 'PDO extension',
	'required' => FALSE,
	'passed' => $pdo = extension_loaded('pdo') && PDO::getAvailableDrivers(),
	'message' => $pdo ? 'Available drivers: ' . implode(' ', PDO::getAvailableDrivers()) : NULL,
	'description' => 'PDO extension or PDO drivers are absent. You will not be able to use <code>Nette\Database</code>.',
);

$tests[] = array(
	'title' => 'Multibyte String extension',
	'required' => FALSE,
	'passed' => extension_loaded('mbstring'),
	'description' => 'Multibyte String extension is absent. Some internationalization components may not work properly.',
);

$tests[] = array(
	'title' => 'Multibyte String function overloading',
	'required' => TRUE,
	'passed' => !extension_loaded('mbstring') || !(mb_get_info('func_overload') & 2),
	'message' => 'Disabled',
	'errorMessage' => 'Enabled',
	'description' => 'Multibyte String function overloading is enabled. Nette Framework requires this to be disabled. If it is enabled, some string function may not work properly.',
);

$tests[] = array(
	'title' => 'Memcache extension',
	'required' => FALSE,
	'passed' => extension_loaded('memcache'),
	'description' => 'Memcache extension is absent. You will not be able to use <code>Nette\Caching\Storages\MemcachedStorage</code>.',
);

$tests[] = array(
	'title' => 'GD extension',
	'required' => FALSE,
	'passed' => extension_loaded('gd'),
	'description' => 'GD extension is absent. You will not be able to use <code>Nette\Image</code>.',
);

$tests[] = array(
	'title' => 'Bundled GD extension',
	'required' => FALSE,
	'passed' => extension_loaded('gd') && GD_BUNDLED,
	'description' => 'Bundled GD extension is absent. You will not be able to use some functions such as <code>Nette\Image::filter()</code> or <code>Nette\Image::rotate()</code>.',
);

$tests[] = array(
	'title' => 'Fileinfo extension or mime_content_type()',
	'required' => FALSE,
	'passed' => extension_loaded('fileinfo') || function_exists('mime_content_type'),
	'description' => 'Fileinfo extension or function <code>mime_content_type()</code> are absent. You will not be able to determine mime type of uploaded files.',
);

$tests[] = array(
	'title' => 'HTTP_HOST or SERVER_NAME',
	'required' => TRUE,
	'passed' => isset($_SERVER['HTTP_HOST']) || isset($_SERVER['SERVER_NAME']),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => 'Either <code>$_SERVER["HTTP_HOST"]</code> or <code>$_SERVER["SERVER_NAME"]</code> must be available for resolving host name.',
);

$tests[] = array(
	'title' => 'REQUEST_URI or ORIG_PATH_INFO',
	'required' => TRUE,
	'passed' => isset($_SERVER['REQUEST_URI']) || isset($_SERVER['ORIG_PATH_INFO']),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => 'Either <code>$_SERVER["REQUEST_URI"]</code> or <code>$_SERVER["ORIG_PATH_INFO"]</code> must be available for resolving request URL.',
);

$tests[] = array(
	'title' => 'SCRIPT_NAME or DOCUMENT_ROOT & SCRIPT_FILENAME',
	'required' => TRUE,
	'passed' => isset($_SERVER['SCRIPT_NAME']) || isset($_SERVER['DOCUMENT_ROOT'], $_SERVER['SCRIPT_FILENAME']),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => '<code>$_SERVER["SCRIPT_NAME"]</code> or <code>$_SERVER["DOCUMENT_ROOT"]</code> with <code>$_SERVER["SCRIPT_FILENAME"]</code> must be available for resolving script file path.',
);

$tests[] = array(
	'title' => 'REMOTE_ADDR or php_uname("n")',
	'required' => TRUE,
	'passed' => isset($_SERVER['REMOTE_ADDR']) || function_exists('php_uname'),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => '<code>$_SERVER["REMOTE_ADDR"]</code> or <code>php_uname("n")</code> must be available for detecting development / production mode.',
);

paint($tests);



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

	require TEMPLATE_FILE;
}



/**
 * Gets the boolean value of a configuration option.
 * @param  string  configuration option name
 * @return bool
 */
function iniFlag($var)
{
	$status = strtolower(ini_get($var));
	return $status === 'on' || $status === 'true' || $status === 'yes' || (int) $status;
}
