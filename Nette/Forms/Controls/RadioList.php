<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette,
	Nette\Utils\Html;


/**
 * Set of radio button controls.
 *
 * @author     David Grudl
 *
 * @property-read Nette\Utils\Html $separatorPrototype
 * @property-read Nette\Utils\Html $containerPrototype
 */
class RadioList extends ChoiceControl
{
	/** @var Nette\Utils\Html  separator element template */
	protected $separator;

	/** @var Nette\Utils\Html  container element template */
	protected $container;


	/**
	 * @param  string  label
	 * @param  array   options from which to choose
	 */
	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->control->type = 'radio';
		$this->container = Html::el();
		$this->separator = Html::el('br');
	}


	/**
	 * Returns selected radio value.
	 * @return mixed
	 */
	public function getValue($raw = FALSE)
	{
		if ($raw) {
			trigger_error(__METHOD__ . '(TRUE) is deprecated; use getRawValue() instead.', E_USER_DEPRECATED);
			return $this->getRawValue();
		}
		return parent::getValue();
	}


	/**
	 * Returns separator HTML element template.
	 * @return Nette\Utils\Html
	 */
	public function getSeparatorPrototype()
	{
		return $this->separator;
	}


	/**
	 * Returns container HTML element template.
	 * @return Nette\Utils\Html
	 */
	public function getContainerPrototype()
	{
		return $this->container;
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl($key = NULL)
	{
		if ($key !== NULL) {
			trigger_error('Partial ' . __METHOD__ . '() is deprecated; use getControlPart() instead.', E_USER_DEPRECATED);
			return $this->getControlPart($key);
		}

		$input = parent::getControl();
		$ids = array();
		foreach ($this->getItems() as $value => $label) {
			$ids[$value] = $input->id . '-' . $value;
		}

		return $this->container->setHtml(
			Nette\Forms\Helpers::createInputList(
				$this->translate($this->getItems()),
				array_merge($input->attrs, array(
					'id:' => $ids,
					'checked?' => $this->value,
					'disabled:' => $this->disabled,
					'data-nette-rules:' => array(key($ids) => $input->attrs['data-nette-rules']),
				)),
				array('for:' => $ids) + $this->label->attrs,
				$this->separator
			)
		);
	}


	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getLabel($caption = NULL, $key = NULL)
	{
		if ($key !== NULL) {
			trigger_error('Partial ' . __METHOD__ . '() is deprecated; use getLabelPart() instead.', E_USER_DEPRECATED);
			return $this->getLabelPart($key);
		}
		return parent::getLabel($caption)->for(NULL);
	}


	/**
	 * @return Nette\Utils\Html
	 */
	public function getControlPart($key)
	{
		return parent::getControl()->addAttributes(array(
			'id' => $this->getHtmlId() . '-' . $key,
			'checked' => in_array($key, (array) $this->value),
			'disabled' => is_array($this->disabled) ? isset($this->disabled[$key]) : $this->disabled,
			'value' => $key,
		));
	}


	/**
	 * @return Nette\Utils\Html
	 */
	public function getLabelPart($key)
	{
		return parent::getLabel($this->items[$key])->for($this->getHtmlId() . '-' . $key);
	}

}
