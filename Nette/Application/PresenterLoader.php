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



require_once dirname(__FILE__) . '/../Application/IPresenterLoader.php';



/**
 * Default presenter loader.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 */
class PresenterLoader implements IPresenterLoader
{
	/** @var bool */
	public $caseSensitive = FALSE;



	/**
	 * @param  string  presenter name
	 * @return string  class name
     * @throws ApplicationException
	 */
	public function getPresenterClass(& $name)
	{
		$class = $this->formatPresenterClass($name);

		if (!class_exists($class)) {
			// internal autoloading
			$file = $this->formatPresenterFile($name);
			if (is_file($file) && is_readable($file)) {
				include_once $file;
			}

			if (!class_exists($class)) {
				throw new ApplicationException("Cannot load presenter '$name', missing class '$class' in '$file'.");
			}
		}

		$reflection = new ReflectionClass($class);

		// canonicalize presenter name
		$realName = $this->unformatPresenterClass($reflection->getName());
		if ($name !== $realName) {
			if ($this->caseSensitive) {
				throw new ApplicationException("Cannot load presenter '$name' - case mismatch.");
			} else {
				$name = $realName;
			}
		}

		if (!$reflection->implementsInterface(/*Nette::Application::*/'IPresenter')) {
			throw new ApplicationException("Invalid presenter '$name'.");
		}

		if ($reflection->isAbstract()) {
			throw new ApplicationException("Invalid presenter '$name' - abstract class.");
		}

		return $class;
	}



	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 */
	public static function formatPresenterClass($name)
	{
		// PHP 5.3
		/*return str_replace(':', '::', $name) . 'Presenter';*/
		return strtr($name, ':', '_') . 'Presenter';
	}



	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 */
	public static function unformatPresenterClass($name)
	{
		// PHP 5.3
		/*return str_replace('::', ':', substr($name, 0, -9));*/
		return strtr(substr($name, 0, -9), '_', ':');
	}



	/**
	 * Formats presenter class file name.
	 * @param  string
	 * @return string
	 */
	public static function formatPresenterFile($name)
	{
		$name = str_replace(':', 'Module/', $name);
		$name = /*Nette::*/Environment::getVariable('presentersDir') . '/' . $name . 'Presenter.php';
		return $name;
	}

}
