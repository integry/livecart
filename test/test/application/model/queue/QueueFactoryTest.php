<?php
require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.queue.*');

/**
 *
 * @package test.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
class QueueFactoryTest extends LiveCartTest
{
	private $queueName = 244566;
    private $queue;

	public function testGetQueueMethods()
	{
		$queueMethods = QueueFactory::getQueueMethods($this->getApplication());

		$this->assertContains('NoQueue', $queueMethods);
		$this->assertContains('MysqlQueue', $queueMethods);
		$this->assertContains('SemaphoreQueue', $queueMethods);
	}

	public function testGetQueue()
	{
		$queueMethods = QueueFactory::getQueueMethods($this->getApplication());
		$this->queueName = 244566;

		foreach ($queueMethods as $method)
		{
			$this->getApplication()->getConfig()->set('QUEUE_METHOD', $method);
			$this->queue = QueueFactory::getQueue($this->getApplication()->getConfig(), $this->queueName);

			$this->assertIsA($this->queue, $method);
		}

	}
}