<?php
require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.queue.MysqlQueue');

/**
 *
 * @package test.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
class MysqlQueueTest extends LiveCartTest
{
	/**
	 * @var
	 */
	private $queue;

	/**
	 * @return array
	 */
	public function getUsedSchemas()
	{
		return array(
			'MysqlQueue'
		);
	}

	/**
	 * Setup the queue and clear it in case it's not empty
	 */
	public function setUp()
	{
		parent::setUp();
		ActiveRecordModel::executeUpdate('DELETE FROM MysqlQueue');
		$this->queue = MysqlQueue::getNewInstance();
		$this->queue->setQueueName(2534756);

		//Empty the queue in case there was some leftovers.
		while(!is_null($this->queue->receive())){};
	}

	/**
	 * Test the MysqlQueue for sending and retrieval in the correct order
	 */
	public function testSendAndReceive()
	{
		$message1 = "SomeRandomMessage".rand();
		$message2 = "SomeRandomMessage".rand();
		$this->queue->send($message1, 1);
		$this->queue->send($message2, 1);

		$this->assertEqual($this->queue->receive(), $message1);
		$this->assertEqual($this->queue->receive(), $message2);
	}

	/**
	 * Test the MysqlQueue for sending and retrieval messages with different priority
	 */
	public function testQueuePriority()
	{
		$message1 = "SomeRandomMessage".rand();
		$message2 = "SomeRandomMessage".rand();
		$this->queue->send($message1, 1);
		$this->queue->send($message2, 2);

		//The latter message should be received first, as it has a higher priority.
		$this->assertEqual($this->queue->receive(), $message2);
		$this->assertEqual($this->queue->receive(), $message1);
	}

} 