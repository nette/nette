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



require_once dirname(__FILE__) . '/../ComponentContainer.php';

require_once dirname(__FILE__) . '/../Application/ISignalReceiver.php';

require_once dirname(__FILE__) . '/../Application/IStatePersistent.php';



/**
 * PresenterComponent is the base class for all presenters components.
 *
 * Components are persistent objects located on a presenter. They have ability to own
 * other child components, and interact with user. Components have properties
 * for storing their status, and responds to user command.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 */
abstract class PresenterComponent extends /*Nette::*/ComponentContainer implements ISignalReceiver, IStatePersistent
{
	/** @var array */
	protected $params = array();



	/**
	 * Returns the presenter where this component belongs to.
	 * @param  bool
	 * @return Presenter|NULL
	 */
	public function getPresenter($need = TRUE)
	{
		return $this->lookup('Nette::Application::Presenter', $need);
	}



	/**
	 * Returns a fully-qualified name that uniquely identifies the component.
	 * within the presenter hierarchy.
	 * @return string
	 */
	public function getUniqueId()
	{
		return $this->lookupPath('Nette::Application::Presenter');
	}



	/**
	 * Forwards notification messages to all components in hierarchy. Do not call directly.
	 * @param  Nette::IComponent
	 * @param  mixed
	 * @return void
	 */
	protected function notification(/*Nette::*/IComponent $sender, $message)
	{
		parent::notification($sender, $message);

		$presenter = $this->getPresenter(FALSE);
		if ($presenter !== NULL) {
			if ($message === self::HIERARCHY_DETACH) {
				// is called before sender's parent is about to be detached
				$presenter->unregisterComponent($this);

			} elseif ($message === self::HIERARCHY_ATTACH) {
				// is called after sender's parent was attached
				$presenter->registerComponent($this->getUniqueId(), $this);
			}
		}
	}



	protected function tryCall($method, array $params)
	{
		$class = $this->getClass();
		if (PresenterHelpers::isMethodCallable($class, $method)) {
			$args = PresenterHelpers::paramsToArgs($class, $method, $params);
			call_user_func_array(array($this, $method), $args);
			return TRUE;
		}
		return FALSE;
	}



	/********************* interface IStatePersistent ****************d*g**/



	/**
	 * Loads state informations.
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		$this->params = $params;
		foreach (PresenterHelpers::getPersistentParams($this->getClass()) as $nm => $l)
		{
			if (!isset($params[$nm])) continue; // ignore NULL values
			if ($l['type']) settype($params[$nm], $l['type']);
			$this->$nm = & $params[$nm];
		}
	}



	/**
	 * Saves state informations for next request.
	 * @param  array
	 * @param  portion specified by class name (used by Presenter)
	 * @return void
	 */
	public function saveState(array & $params, $forClass = NULL)
	{
		if ($forClass === NULL) {
			$forClass = $this->getClass();
		}

		foreach (PresenterHelpers::getPersistentParams($forClass) as $nm => $l)
		{
			if (!($this instanceof $l['since'])) continue;

			if (isset($params[$nm])) {
				$val = $params[$nm]; // injected value

			} elseif (array_key_exists($nm, $params)) {
				continue; // i.e. $params[$nm] === NULL -> means skip

			} else {
				$val = $this->$nm; // object property value
			}

			// only NULLs, scalar, arrays and IStatePersistent are allowed
			if ($val === NULL || is_scalar($val) || is_array($val)) {
				if ($l['type']) settype($val, $l['type']);

				if ($val === $l['def']) {
					$params[$nm] = NULL;
				} else {
					$params[$nm] = $val;
				}

			} else {
				throw new InvalidStateException("Persistent parameter must be scalar or array, '$this->class::\$$nm' is " . gettype($val));
			}
		}
	}



	/********************* interface ISignalReceiver ****************d*g**/


	/**
	 * Calls signal handler method.
	 * @param  string
	 * @return void
	 * @throws BadSignalException if there is not handler method
	 */
	public function signalReceived($signal)
	{
		if (!$this->tryCall($this->formatSignalMethod($signal), $this->params)) {
			throw new BadSignalException("There is no handler for signal '$signal' in '{$this->getClass()}' class.");
		}
	}



	/**
	 * Formats signal handler method name -> case sensitivity doesn't matter.
	 * @param  string
	 * @return string
	 */
	protected function formatSignalMethod($signal)
	{
		return $signal == NULL ? NULL : 'handle' . $signal; // intentionally ==
	}



	/********************* navigation ****************d*g**/



	/**
	 * Generates URL to signal.
	 * @param  string
	 * @param  array|mixed
	 * @return string
	 * @throws InvalidLinkException
	 */
	public function link($signal, $args = array())
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}

		try {
			// exclamation is not required, every destinations are signals
			$signal = rtrim($signal, '!');
			if ($signal == NULL) {  // intentionally ==
				throw new InvalidLinkException("Signal must be non-empty string.");

			} elseif ($signal === 'this') {
				$signal = '';

			} else {
				$class = $this->getClass();
				$method = $this->formatSignalMethod($signal);
				if (!PresenterHelpers::isMethodCallable($class, $method)) {
					throw new InvalidLinkException("Unknown signal '$class:$signal!'.");
				}

				if ($args) {
					PresenterHelpers::argsToParams($class, $method, $args);
				}
			}

			if ($args) {
				$this->saveState($args);
			}

			return $this->getPresenter()->createSubRequest($this->getUniqueId(), $signal, $args);

		} catch (InvalidLinkException $e) {
			return $this->getPresenter()->handleInvalidLink($e);
		}
	}



	public function lazyLink($destination, $args = array())
	{
		return new Link($this, $destination, $args);
	}



	public function ajaxLink($destination, $args = array())
	{
		return $this->getPresenter()->getAjaxDriver()->link($destination === NULL ? NULL : $this->link($destination, $args));
	}



	/**
	 * Redirect to another presenter, view or signal.
	 * @param  string
	 * @param  array
	 * @param  int HTTP error code
	 * @return void
	 */
	public function redirect($destination, $args = NULL, $code = /*Nette::Web::*/IHttpResponse::S303_POST_GET)
	{
		if ($args === NULL) $args = array();
		$this->getPresenter()->redirectUri($this->link($destination, $args), $code);
	}

}
