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



require_once dirname(__FILE__) . '/../Application/PresenterComponent.php';

require_once dirname(__FILE__) . '/../Application/IRenderable.php';



/**
 * Control is renderable component.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Application
 */
abstract class Control extends PresenterComponent implements IPartiallyRenderable
{
	/** @var Nette\Templates\ITemplate */
	private $template;

	/** @var bool  helper for beginSnippet() & endSnippet() */
	protected static $outputAllowed = TRUE;

	/** @var array see invalidateControl() & validateControl() */
	private $invalidSnippets = array();

	/** @var array used by beginSnippet() & endSnippet() */
	private static $beginedSnippets = array();



	/********************* template factory ****************d*g**/



	/**
	 * @return Nette\Templates\ITemplate
	 */
	final public function getTemplate()
	{
		if ($this->template === NULL) {
			$value = $this->createTemplate();
			if (!($value instanceof /*Nette\Templates\*/ITemplate || $value === NULL)) {
				$class = get_class($value);
				throw new /*\*/UnexpectedValueException("The Nette\Templates\ITemplate object was expected, '$class' was given.");
			}
			$this->template = $value;
		}
		return $this->template;
	}



	/**
	 * @return Nette\Templates\ITemplate
	 */
	protected function createTemplate()
	{
		$template = new /*Nette\Templates\*/Template;
		$template->component = $this; // DEPRECATED!
		$template->control = $this;
		$template->presenter = $this->getPresenter(FALSE);
		$template->baseUri = /*Nette\*/Environment::getVariable('baseUri');
		return $template;
	}



	/********************* rendering ****************d*g**/



	/**
	 * Forces control or its snippet to repaint.
	 * @param  string
	 * @return void
	 */
	public function invalidateControl($snippet = NULL, $meta = NULL)
	{
		$this->invalidSnippets[$snippet] = (array) $meta;
	}



	/**
	 * Allows control or its snippet to not repaint.
	 * @param  string
	 * @return void
	 */
	public function validateControl($snippet = NULL)
	{
		if ($snippet === NULL) {
			$this->invalidSnippets = array();

		} else {
			unset($this->invalidSnippets[$snippet]);
		}
	}



	/**
	 * Is required to repaint the control or its snippet?
	 * @param  string  snippet name
	 * @return bool
	 */
	public function isControlInvalid($snippet = NULL)
	{
		if ($snippet === NULL) {
			if (count($this->invalidSnippets) > 0) {
				return TRUE;

			} else {
				foreach ($this->getComponents() as $component) {
					if ($component instanceof IRenderable && $component->isControlInvalid()) {
						// $this->invalidSnippets['__child'] = TRUE; // as cache
						return TRUE;
					}
				}
				return FALSE;
			}

		} else {
			return isset($this->invalidSnippets[NULL]) || isset($this->invalidSnippets[$snippet]);
		}
	}



	/**
	 *
	 * @return bool
	 */
	public static function isOutputAllowed()
	{
		return self::$outputAllowed;
	}



	/**
	 *
	 * @return bool
	 */
	public function getSnippetId($name = 'main')
	{
		// HTML 4 ID & NAME: [A-Za-z][A-Za-z0-9:_.-]*
		return $this->getUniqueId() . ':' . $name;
	}



	/**
	 * Starts conditional snippet rendering. Returns TRUE if snippet was started.
	 * @param  string  snippet name
	 * @param  string  start element
	 * @return bool
	 */
	public function beginSnippet($name = 'main', $startTag = 'div')
	{
		$id = $this->getSnippetId($name);

		if (self::$outputAllowed) {
			$startTag = trim($startTag, '<>');
			self::$beginedSnippets[] = array($id, NULL, $startTag);
			echo '<', $startTag, ' id="', $id, '">';

		} elseif (isset($this->invalidSnippets[$name])) {
			self::$outputAllowed = TRUE;
			ob_start();
			self::$beginedSnippets[] = array($id, ob_get_level(), $this->invalidSnippets[$name]);

		} elseif (isset($this->invalidSnippets[NULL])) {
			self::$outputAllowed = TRUE;
			ob_start();
			self::$beginedSnippets[] = array($id, ob_get_level(), NULL);

		} else {
			return FALSE;
		}
		return TRUE;
	}



	/**
	 * Finist conditional snippet rendering.
	 * @param  string  snippet name
	 * @return void
	 */
	public function endSnippet($name = NULL)
	{
		list($id, $level, $endTag) = array_pop(self::$beginedSnippets);

		if ($name != NULL && $id !== ($this->getUniqueId() . ':' . $name)) {
			throw new /*\*/InvalidStateException("Snippet '$name' cannot be ended here.");
		}

		if ($level !== NULL) {
			if ($level !== ob_get_level()) {
				throw new /*\*/InvalidStateException("Snippet '$name' cannot be ended here.");
			}
			$this->getPresenter()->getAjaxDriver()->updateSnippet($id, ob_get_clean(), $endTag);
			self::$outputAllowed = FALSE;

		} elseif (self::$outputAllowed) {
			echo '</', $endTag, '>';
		}

		unset(self::$beginedSnippets[$id]);
	}

}
