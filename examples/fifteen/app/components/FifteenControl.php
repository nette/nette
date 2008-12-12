<?php

/**
 * Nette Framework "Fifteen" Example
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 */



/**
 * The Fifteen game control
 *
 * @author     David Grudl
 */
class FifteenControl extends /*Nette\Application\*/Control
{
	/** @var int */
	protected $width = 4;

	/** @var array of function ($sender) */
	public $onAfterClick;

	/** @var array of function ($sender, $round) */
	public $onGameOver;

	/** @persistent array */
	public $order;

	/** @persistent int */
	public $round = 0;

	/** @var bool */
	public $useAjax = TRUE;




	public function __construct($parent = NULL, $name = NULL)
	{
		if (empty($this->order)) {
			$this->order = range(0, $this->width * $this->width - 1);
		}
		parent::__construct($parent, $name);
	}



	public function handleClick($x, $y)
	{
		if (!$this->isClickable($x, $y)) {
			throw new /*Nette\Application\*/BadSignalException('Action not allowed.');
		}

		if ($this->presenter->isAjax()) {
			$ajax = $this->presenter->ajaxDriver;
			$pos = $x + $y * $this->width;
			$empty = $this->searchEmpty();
			$id = $this->getSnippetId();
			if ($pos === $empty - 1) $ajax->events[] = array('fifteen.move', $id, $x, $y, -1, 0);
			elseif ($pos === $empty + 1) $ajax->events[] = array('fifteen.move', $id, $x, $y, +1, 0);
			elseif ($pos > $empty) $ajax->events[] = array('fifteen.move', $id, $x, $y, 0, +1);
			else $ajax->events[] = array('fifteen.move', $id, $x, $y, 0, -1);
		}

		$this->move($x, $y);
		$this->round++;
		$this->onAfterClick($this);

		if ($this->order == range(0, $this->width * $this->width - 1)) {
			$this->onGameOver($this, $this->round);
		}
	}



	public function handleShuffle()
	{
		$i = 100;
		while ($i) {
			$x = rand(0, $this->width - 1);
			$y = rand(0, $this->width - 1);
			if ($this->isClickable($x, $y)) {
				$this->move($x, $y);
				$i--;
			}
		}
		$this->round = 0;
	}



	public function getRound()
	{
		return $this->round;
	}



	public function isClickable($x, $y)
	{
		$pos = $x + $y * $this->width;
		$empty = $this->searchEmpty();
		$y = (int) ($empty / $this->width);
		$x = $empty % $this->width;
		if ($x > 0 && $pos === $empty - 1) return TRUE;
		if ($x < $this->width-1 && $pos === $empty + 1) return TRUE;
		if ($y > 0 && $pos === $empty - $this->width) return TRUE;
		if ($y < $this->width-1 && $pos === $empty + $this->width) return TRUE;
		return FALSE;
	}



	private function move($x, $y)
	{
		$pos = $x + $y * $this->width;
		$emptyPos = $this->searchEmpty();
		$this->order[$emptyPos] = $this->order[$pos];
		$this->order[$pos] = 0;
	}



	private function searchEmpty()
	{
		return array_search(0, $this->order);
	}



	public function render()
	{
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/FifteenControl.phtml');
		$template->registerFilter(/*Nette\Templates\*/'CurlyBracketsFilter::invoke');
		$template->width = $this->width;
		$template->order = $this->order;
		$template->useAjax = $this->useAjax;
		$template->render();
	}



	/**
	 * Loads params
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		if (isset($params['order'])) {
			$params['order'] = explode('.', (string) $params['order']);

			// validate
			$copy = $params['order'];
			sort($copy);
			if ($copy != range(0, $this->width * $this->width - 1)) {
				unset($params['order']);
			}
		}

		parent::loadState($params);
	}



	/**
	 * Save params
	 * @param  array
	 * @return void
	 */
	public function saveState(array & $params)
	{
		parent::saveState($params);
		if (isset($params['order'])) {
			$params['order'] = implode('.', $params['order']);
		}
	}

}
