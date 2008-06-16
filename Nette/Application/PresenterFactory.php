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



require_once dirname(__FILE__) . '/../Application/IPresenterFactory.php';



/**
 * Default presenter factory.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 */
class PresenterFactory implements IPresenterFactory
{

	/**
	 * @param  string  presenter name
	 * @return string  class name
     * @throws ApplicationException
	 */
	public function getPresenterClass($name)
	{
		$class = $this->formatPresenterClass($name);

		if (!class_exists($class)) {
			// internal autoloading
			if (!preg_match('#^(:[a-z][a-z0-9]*)*$#i', ':' . $name)) {
				throw new /*::*/InvalidArgumentException("Invalid presenter name '$name'.");
			}

			$file = $this->formatPresenterFile($name);
			if (is_file($file) && is_readable($file)) {
				include_once $file;

				if (!class_exists($class)) {
					throw new ApplicationException("Cannot load presenter '$name', missing class '$class' in '$file'.");
				}
			}
		}

		$reflection = new ReflectionClass($class);

		if (!$reflection->implementsInterface(/*Nette::Application::*/'IPresenter')) {
			throw new ApplicationException("Invalid presenter '$name'.");
		}

		if ($reflection->isAbstract()) {
			throw new ApplicationException("Invalid (abstract) presenter '$name'.");
		}

		return $class;
	}



	/**
	 * Formats presenter class name -> case sensitivity doesn't matter.
	 * @param  string
	 * @return string
	 */
	public static function formatPresenterClass($name)
	{
		// PHP 5.3
		// return str_replace(':', '::', $name) . 'Presenter';
		return strtr($name, ':', '_') . 'Presenter';
	}



	/**
	 * Formats presenter class name -> case sensitivity DOES matter.
	 * @param  string
	 * @return string
	 */
	public static function formatPresenterFile($name)
	{
		$name = strtr($name, ':', ' ');
		$name = ucwords(strtolower($name));
		$name = strtr($name, ' ', '/');
		$name = /*Nette::*/Environment::getVariable('presentersDir') . '/' . $name . 'Presenter.php';
		return $name;
	}

}
