<?php

/**
 * Test: Nette\DI\Helpers::getInjectProperties()
 *
 * @author     Jan Tvrdík
 * @package    Nette\DI
 */

namespace A
{
	class AClass
	{
		/** @var AInjected @inject */
		public $varA;

		/** @var B\BInjected @inject */
		public $varB;

		/** @var \A\AInjected @inject */
		public $varC;

		/** @var AInjected */
		public $varD;

		/** @var AInjected @inject */
		protected $varE;
	}

	class AInjected
	{

	}
}

namespace A\B
{
	class BClass extends \A\AClass
	{
		/** @var BInjected @inject */
		public $varF;
	}

	class BInjected
	{

	}
}

namespace
{
	use Nette\DI\Helpers;
	use Nette\Reflection\ClassType;

	require __DIR__ . '/../bootstrap.php';


	$refA = ClassType::from('A\AClass');
	$refB = ClassType::from('A\B\BClass');

	Assert::same( array(
		'varA' => 'A\AInjected',
		'varB' => 'A\B\BInjected',
		'varC' => '\A\AInjected',
	), Helpers::getInjectProperties($refA) );

	Assert::same( array(
		'varF' => 'A\B\BInjected',
		'varA' => 'A\AInjected',
		'varB' => 'A\B\BInjected',
		'varC' => '\A\AInjected',
	), Helpers::getInjectProperties($refB) );
}
