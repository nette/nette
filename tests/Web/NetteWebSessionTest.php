<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 * @version    $Id$
 */

/*use Nette\Debug;*/
/*use Nette\Web\Session;*/
/*use Nette\Web\SessionNamespace;*/



require_once 'PHPUnit/Framework.php';

require_once '../../Nette/loader.php';



/**
 * @package    Nette\Web
 * @subpackage UnitTests
 */
class NetteWebSessionTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Session
	 */
	private $session;



	/**
	 * This method is called before a test is executed.
	 * @return void
	 */
	protected function setUp()
	{
		$this->session = new Session;
	}



	/**
	 * Cleanup operations after each test method is run.
	 * @return void
	 */
	protected function tearDown()
	{
		if ($this->session->isStarted()) {
			$this->session->destroy();
		}
	}



	/**
	 * Serializes iterator.
	 * @param  Iterator
	 * @return string
	 */
	private static function serialize($iterator)
	{
		$s = '';
		foreach ($iterator as $key => $val) {
			$s .= "$key=$val;";
		}
		return $s;
	}



	/**
	 * Test session id regeneration.
	 * @return void
	 */
	public function testRegenerateId()
	{
		$this->session->start();
		$oldId = $this->session->getId();
		$this->session->regenerateId();
		$newId = $this->session->getId();
		$this->assertNotEquals($newId, $oldId);
	}



	/**
	 * Test for namespace class.
	 * @return void
	 */
	public function testGetNamespace()
	{
		$s = $this->session->getNamespace('default');
		$this->assertType(/*Nette\Web\*/'SessionNamespace', $s);

		$this->setExpectedException('InvalidArgumentException', 'must be a non-empty string');
		$s = $this->session->getNamespace('');
	}



	/**
	 * Test for retrieval of non-existent keys in a namespace.
	 * @return void
	 */
	public function testNamespaceGetNull()
	{
		$s = $this->session->getNamespace('default');
		$this->assertNull($s->undefined, 'Getting value of non-existent key failed to return NULL.');
	}



	/**
	 * Test for existence of some namespaces.
	 * @return void
	 */
	public function testHasNamespace()
	{
		$this->assertFalse($this->session->hasNamespace('trees'),
			'hasNamespace() should have returned FALSE for a namespace with no keys set');

		$s = $this->session->getNamespace('trees');
		$this->assertFalse($this->session->hasNamespace('trees'),
			'hasNamespace() should have returned FALSE for a namespace with no keys set');

		$s->hello = 'world';
		$this->assertTrue($this->session->hasNamespace('trees'),
			'hasNamespace() should have returned TRUE for a namespace with keys set');
	}



	/**
	 * Test for proper separation of namespace spaces.
	 * @return void
	 */
	public function testInitNamespaces()
	{
		$s1 = $this->session->getNamespace('namespace1');
		$s1b = $this->session->getNamespace('namespace1');
		$s2 = $this->session->getNamespace('namespace2');
		$s2b = $this->session->getNamespace('namespace2');
		$s3 = $this->session->getNamespace('default');
		$s3b = $this->session->getNamespace('default');
		$s1->a = 'apple';
		$s2->a = 'pear';
		$s3->a = 'orange';
		$this->assertTrue($s1->a !== $s2->a && $s1->a !== $s3->a && $s2->a !== $s3->a, 'Session improperly shared namespaces');
		$this->assertTrue($s1->a === $s1b->a, 'Session namespace error');
		$this->assertTrue($s2->a === $s2b->a, 'Session namespace error');
		$this->assertTrue($s3->a === $s3b->a, 'Session namespace error');
	}



	/**
	 * Test iteration; expect native PHP foreach statement is able to properly iterate all items in a session namespace.
	 * @return void
	 */
	public function testGetIterator()
	{
		$s = $this->session->getNamespace('one');
		$s->a = 'apple';
		$s->p = 'pear';
		$s['o'] = 'orange';
		$result = $this->serialize($s->getIterator());
		$this->assertEquals('a=apple;p=pear;o=orange;', $result, 'Iteration over default Session namespace failed');

		$s = $this->session->getNamespace('two');
		$s->g = 'guava';
		$s->p = 'peach';
		$s['p'] = 'plum';
		$result = $this->serialize($s->getIterator());
		$this->assertEquals('g=guava;p=plum;', $result,	'Iteration over named Session namespace failed');
	}



	/**
	 * Test removing of namespace.
	 * @return void
	 */
	public function testRemove()
	{
		$s = $this->session->getNamespace('three');
		$s->a = 'apple';
		$s->p = 'papaya';
		$s['c'] = 'cherry';

		$s = $this->session->getNamespace('three');
		$result = $this->serialize($s->getIterator());
		$this->assertEquals('a=apple;p=papaya;c=cherry;', $result);

		$s->remove();
		$result = $this->serialize($s->getIterator());
		$this->assertEquals('', $result, 'remove() did not remove data from namespace');
	}



	/**
	 * Test unset() keys in namespace.
	 * @return void
	 */
	public function testUnset()
	{
		$s = $this->session->getNamespace('four');
		$s->a = 'apple';
		$s->p = 'papaya';
		$s['c'] = 'cherry';
		$this->assertTrue(isset($s['p']));
		$this->assertTrue(isset($s->c));

		$s = $this->session->getNamespace('four');
		unset($s['a']);
		unset($s->p);
		unset($s->c);
		$result = $this->serialize($s->getIterator());
		$this->assertEquals('', $result, 'unset() did not remove keys from namespace');
	}



	/**
	 * Test expiration of namespaces and namespace variables.
	 * @return void
	 */
	public function testSetExpiration()
	{
		// try to expire whole namespace
		$s = $this->session->getNamespace('expire');
		$s->a = 'apple';
		$s->p = 'pear';
		$s['o'] = 'orange';
		$s->setExpiration(5);

		$this->session->close();
		sleep(6);
		$this->session->start();

		$s = $this->session->getNamespace('expire');
		$result = $this->serialize($s->getIterator());
		$this->assertEquals('', $result, 'iteration over named Session namespace failed');

		// try to expire only 1 of the keys
		$s = $this->session->getNamespace('expireSingle');
		$s->setExpiration(5, 'g');
		$s->g = 'guava';
		$s->p = 'plum';

		$this->session->close();
		sleep(6);
		$this->session->start();

		$s = $this->session->getNamespace('expireSingle');
		$result = $this->serialize($s->getIterator());
		$this->assertEquals('p=plum;', $result,	'iteration over named Session namespace failed');
	}

}


ob_start();