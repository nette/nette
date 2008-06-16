<?php



class FifteenControl extends Control
{
	const WIDTH = 4;
	const MAX   = 15;  // self::WIDTH * self::WIDTH - 1

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




	protected function constructed()
	{
		if (empty($this->order)) {
			$this->order = range(0, self::MAX);
		}
	}



	public function handleClick($x, $y)
	{
		if (!$this->isClickable($x, $y)) {
			throw new Exception('Action not allowed.');
		}

		$this->move($x, $y);
		$this->round++;
		$this->onAfterClick($this);

		if ($this->order == range(0, self::MAX)) {
			$this->onGameOver($this, $this->round);
		}
	}



	public function handleShuffle()
	{
		for ($i=0; $i<100; $i++) {
			$x = rand(0, self::WIDTH - 1);
			$y = rand(0, self::WIDTH - 1);
			if ($this->isClickable($x, $y)) {
				$this->move($x, $y);
			}
		}
		$this->round = 0;
	}



	public function getRound()
	{
		return $this->round;
	}



	private function isClickable($x, $y)
	{
		$pos = $x + $y * self::WIDTH;
		$empty = $this->searchEmpty();
		$y = (int) ($empty / self::WIDTH);
		$x = $empty % self::WIDTH;
		if ($x > 0 && $pos === $empty - 1) return TRUE;
		if ($x < self::WIDTH-1 && $pos === $empty + 1) return TRUE;
		if ($y > 0 && $pos === $empty - self::WIDTH) return TRUE;
		if ($y < self::WIDTH-1 && $pos === $empty + self::WIDTH) return TRUE;
		return FALSE;
	}



	private function move($x, $y)
	{
		$pos = $x + $y * self::WIDTH;
		$emptyPos = $this->searchEmpty();
		$this->order[$emptyPos] = $this->order[$pos];
		$this->order[$pos] = self::MAX;
	}



	private function searchEmpty()
	{
		return array_search(self::MAX, $this->order);
	}



	public function render()
	{
		if (!$this->beginPartial()) return;

		echo "<table>\n";

		for ($y = 0; $y < self::WIDTH; $y++)
		{
			echo "<tr>\n";

			for ($x = 0; $x < self::WIDTH; $x++)
			{
				$pos = $x + $y * self::WIDTH;
				echo '<td>';

				$clickable = $this->isClickable($x, $y);

				if ($clickable) {
					echo '<a href="', htmlSpecialChars($this->link('Click', $x, $y)), '"';
					if ($this->useAjax) echo ' onclick="return !nette.action(this.href, this)"';
					echo '>';
				}

				echo '<img src="images/', $this->order[$pos], '.jpg" width="100" height="100" alt="', ($this->order[$pos]+1), '" />';

				if ($clickable) echo '</a>';

				echo "</td>\n";
			}

			echo "</tr>\n";
		}

		echo "</table>\n";

		$this->endPartial();
	}



	/**
	 * Loads params
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		if (isset($params['order'])) {
			$params['order'] = explode('.', $params['order']);

			// validate
			$copy = $params['order'];
			sort($copy);
			if ($copy != range(0, self::MAX)) {
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
