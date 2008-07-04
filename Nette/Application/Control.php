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



require_once dirname(__FILE__) . '/../Application/PresenterComponent.php';



/**
 * Control is renderable component.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 */
abstract class Control extends PresenterComponent
{
	/** @var string  default partial name */
	const CONTROL = '_control';

	/** @var bool  helper for beginPartial() & endPartial() */
	private static $invalidBranch = FALSE;

	/** @var array see invalidate() & validate() */
	private $invalidPartials = array();

	/** @var array used by beginPartial() & endPartial() */
	private $beginedPartials = array();

	/** @var bool cache for self::hasInvalidChild() */
	private $hasInvalidChild = NULL;




	/********************* partial rendering ****************d*g**/



	/**
	 * This component's part must be repainted.
	 * @param  string
	 * @return void
	 */
	public function invalidate($partialName = self::CONTROL)
	{
		$this->invalidPartials[$partialName] = TRUE;
	}



	/**
	 * This component's part should not be repainted.
	 * @param  string
	 * @return void
	 */
	public function validate($partialName = self::CONTROL)
	{
		unset($this->invalidPartials[$partialName]);
	}



	/**
	 * Must be the component's part repainted?
	 * @param  string
	 * @return bool
	 */
	public function isInvalid($partialName = self::CONTROL)
	{
		if ($partialName === self::CONTROL) {
			return count($this->invalidPartials) > 0;
		} else {
			return isset($this->invalidPartials[$partialName]);
		}
	}



	/**
	 * Starts conditional partial rendering. Returns TRUE if partial was started.
	 * @param  string  partial name
	 * @param  string  start element
	 * @return bool
	 */
	public function beginPartial($name = self::CONTROL, $mask = '<div %id>')
	{
		if (isset($this->beginedPartials[$name])) {
			throw new /*::*/InvalidStateException("Partial '$name' has been already started.");
		}

		// HTML 4 ID & NAME: [A-Za-z][A-Za-z0-9:_.-]*
		$id = $this->getUniqueId() . ':' . $name; // TODO: append counter

		if ($this->getPresenter()->isPartialMode()) {
			if (self::$invalidBranch) {
				$this->beginedPartials[$name] = array($id, NULL);

			} elseif (isset($this->invalidPartials[self::CONTROL]) || isset($this->invalidPartials[$name])) {
				self::$invalidBranch = TRUE;
				ob_start();
				$this->beginedPartials[$name] = array($id, ob_get_level());

			} elseif ($this->hasInvalidChild()) {
				$this->beginedPartials[$name] = array($id, NULL);

			} else {
				return FALSE;
			}

		} else {
			$this->beginedPartials[$name] = $id;
			echo str_replace('%id', 'id="' . $id . '"', $mask);
		}

		return TRUE;
	}



	/**
	 * Finist conditional partial rendering.
	 * @param  string  partial name
	 * @param  string  end element
	 * @return void
	 */
	public function endPartial($name = self::CONTROL, $mask = '</div>')
	{
		if (!isset($this->beginedPartials[$name])) {
			throw new /*::*/InvalidStateException("Partial '$name' has not been started.");
		}

		if ($this->getPresenter()->isPartialMode()) {
			list($id, $level) = $this->beginedPartials[$name];
			if ($level !== NULL) {
				if ($level !== ob_get_level()) {
					throw new /*::*/InvalidStateException("Partial '$name' cannot be ended here.");
				}
				$this->getPresenter()->addPartial($id, ob_get_flush());
				self::$invalidBranch = FALSE;
			}

		} else {
			echo str_replace('%id', 'id="' . $this->beginedPartials[$name] . '"', $mask);
		}

		unset($this->beginedPartials[$name]);
	}



	/**
	 * Lookup for invalid children.
	 * @return bool
	 */
	private function hasInvalidChild()
	{
		if ($this->hasInvalidChild === NULL) {
			if (count($this->invalidPartials) > 0) {
				return $this->hasInvalidChild = TRUE;
			}
			foreach ($this->getComponents() as $component) {
				if ($component instanceof Control) {
					if ($component->hasInvalidChild()) {
						return $this->hasInvalidChild = TRUE;
					}
				}
			}
		}
		return $this->hasInvalidChild = FALSE;
	}

}
