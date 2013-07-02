<?php

/**
 * Test: Nette\Caching\Storages\FileJournal basic test.
 *
 * @author     David Grudl
 * @package    Nette\Caching
 */

use Nette\Caching\Storages\FileJournal,
	Nette\Caching\Cache;


require __DIR__ . '/../bootstrap.php';


function check($result, $condition, $name)
{
	Assert::true($condition, $name . ($condition === TRUE ? '' : 'Count: ' . count($result)));
}


$journal = new FileJournal(TEMP_DIR);

$journal->write('ok_test1', array(
	Cache::TAGS => array('test:homepage'),
));

$result = $journal->clean(array(Cache::TAGS => array('test:homepage')));
check($result, (count($result) === 1 and $result[0] === 'ok_test1'), 'One tag');

$journal->write('ok_test2', array(
	Cache::TAGS => array('test:homepage', 'test:homepage2'),
));

$result = $journal->clean(array(Cache::TAGS => array('test:homepage2')));
check($result, (count($result) === 1 and $result[0] === 'ok_test2'), 'Two tags');

$journal->write('ok_test2b', array(
	Cache::TAGS => array('test:homepage', 'test:homepage2'),
));

$result = $journal->clean(array(Cache::TAGS => array('test:homepage', 'test:homepage2')));
check($result, (count($result) === 1 and $result[0] === 'ok_test2b'), 'Two tags b');

$journal->write('ok_test2c', array(
	Cache::TAGS => array('test:homepage', 'test:homepage'),
));

$result = $journal->clean(array(Cache::TAGS => array('test:homepage', 'test:homepage')));
check($result, (count($result) === 1 and $result[0] === 'ok_test2c'), 'Two same tags');

$journal->write('ok_test2d', array(
	Cache::TAGS => array('test:homepage'),
	Cache::PRIORITY => 15,
));

$result = $journal->clean(array(Cache::TAGS => array('test:homepage'), Cache::PRIORITY => 20));
check($result, (count($result) === 1 and $result[0] === 'ok_test2d'), 'Tag and priority');

$journal->write('ok_test3', array(
	Cache::PRIORITY => 10,
));

$result = $journal->clean(array(Cache::PRIORITY => 10));
check($result, (count($result) === 1 and $result[0] === 'ok_test3'), 'Priority only');

$journal->write('ok_test4', array(
	Cache::TAGS => array('test:homepage'),
	Cache::PRIORITY => 10,
));

$result = $journal->clean(array(Cache::TAGS => array('test:homepage')));
check($result, (count($result) === 1 and $result[0] === 'ok_test4'), 'Priority and tag (clean by tag)');

$journal->write('ok_test5', array(
	Cache::TAGS => array('test:homepage'),
	Cache::PRIORITY => 10,
));

$result = $journal->clean(array(Cache::PRIORITY => 10));
check($result, (count($result) === 1 and $result[0] === 'ok_test5'), 'Priority and tag (clean by priority)');

for ($i=1;$i<=10;$i++) {
	$journal->write('ok_test6_'.$i, array(
		Cache::TAGS => array('test:homepage', 'test:homepage/'.$i),
		Cache::PRIORITY => $i,
	));
}

$result = $journal->clean(array(Cache::PRIORITY => 5));
check($result, (count($result) === 5 and $result[0] === 'ok_test6_1'), '10 writes, clean priority lower then 5');

$result = $journal->clean(array(Cache::TAGS => array('test:homepage/7')));
check($result, (count($result) === 1 and $result[0] === 'ok_test6_7'), '10 writes, clean tag homepage/7');

$result = $journal->clean(array(Cache::TAGS => array('test:homepage/4')));
check($result, (count($result) === 0), '10 writes, clean non exists tag');

$result = $journal->clean(array(Cache::PRIORITY => 4));
check($result, (count($result) === 0), '10 writes, clean non exists priority');

$result = $journal->clean(array(Cache::TAGS => array('test:homepage')));
check($result, (count($result) === 4 and $result[0] === 'ok_test6_6'), '10 writes, clean other');

$journal->write('ok_test7ščřžýáíé', array(
	Cache::TAGS => array('čšřýýá', 'ýřžčýž/'.$i)
));

$result = $journal->clean(array(Cache::TAGS => array('čšřýýá')));
check($result, (count($result) === 1 and $result[0] === 'ok_test7ščřžýáíé'), 'Special chars');

$journal->write('ok_test_a', array(
	Cache::TAGS => array('homepage')
));

$journal->write('ok_test_a', array(
	Cache::TAGS => array('homepage')
));

$result = $journal->clean(array(Cache::TAGS => array('homepage')));
check($result, (count($result) === 1 and $result[0] === 'ok_test_a'), 'Duplicates: same tags');

$journal->write('ok_test_b', array(
	Cache::PRIORITY => 12
));

$journal->write('ok_test_b', array(
	Cache::PRIORITY => 12
));

$result = $journal->clean(array(Cache::PRIORITY => 12));
check($result, (count($result) === 1 and $result[0] === 'ok_test_b'), 'Duplicates: same priority');

$journal->write('ok_test_ba', array(
	Cache::TAGS => array('homepage')
));

$journal->write('ok_test_ba', array(
	Cache::TAGS => array('homepage2')
));

$result = $journal->clean(array(Cache::TAGS => array('homepage')));
$result2 = $journal->clean(array(Cache::TAGS => array('homepage2')));
check($result, (count($result2) === 1 and count($result) === 0 and $result2[0] === 'ok_test_ba'), 'Duplicates: differenet tags');

$journal->write('ok_test_baa', array(
	Cache::TAGS => array('homepage', 'aąa')
));

$journal->write('ok_test_baa', array(
	Cache::TAGS => array('homepage2', 'aaa')
));

$result = $journal->clean(array(Cache::TAGS => array('homepage')));
$result2 = $journal->clean(array(Cache::TAGS => array('homepage2')));
check($result, (count($result2) === 1 and count($result) === 0 and $result2[0] === 'ok_test_baa'), 'Duplicates: 2 differenet tags');

$journal->write('ok_test_bb', array(
	Cache::PRIORITY => 15
));

$journal->write('ok_test_bb', array(
	Cache::PRIORITY => 20
));

$result = $journal->clean(array(Cache::PRIORITY => 30));
check($result, (count($result) === 1 and $result[0] === 'ok_test_bb'), 'Duplicates: differenet priorities');


$journal->write('ok_test_all_tags', array(
	Cache::TAGS => array('test:all', 'test:all')
));

$journal->write('ok_test_all_priority', array(
	Cache::PRIORITY => 5,
));

$result = $journal->clean(array(Cache::ALL => TRUE));
$result2 = $journal->clean(array(Cache::TAGS => 'test:all'));
check($result, ($result === NULL and empty($result2)), 'Clean ALL');
