<?php
require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.queue.SemaphoreQueue');

/**
 *
 * @package test.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
class SemaphoreQueueTest  extends LiveCartTest
{
	/**
	 * Test the SemaphoreQueue for sending and retrieval in the correct order
	 */
	public function testSendAndReceive()
	{
		$extensions = get_loaded_extensions();

		//If the extension is not compiled into php, no need to test
		if (!in_array('sysvsem', $extensions)) return;

		$queue = new SemaphoreQueue();
		//Semaphore queue names should be integers
		$queueName = 234767;
		$queue->setQueueName($queueName);

		//Empty the queue in case there was some leftovers.
		while(!is_null($queue->receive())){};

		$message1 = "SomeRandomMessage1:";
		$message2 = "SomeRandomMessage2:";
		$queue->send($message1, 1);
		$queue->send($message2, 1);

		$this->assertEqual($queue->receive(), $message2);
		$this->assertEqual($queue->receive(), $message1);
	}
} 