<?php
/**
 * NoQueue engine model. A dummy model for disabling the queues.
 *
 * @package application.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
ClassLoader::import('application.model.queue.QueueInterface');

/**
 * Class NoQueue
 */
class NoQueue implements QueueInterface{

	/**
	 * @param $queueName
	 */
	public function setQueueName($queueName)
	{

	}

	/**
	 * Does nothing.
	 * @param $message
	 * @param $priority
	 * @return void
	 */
	public function send($message, $priority)
	{

	}

	/**
	 * Does nothing.
	 */
	public function receive()
	{

	}

	/**
	 * Does nothing.
	 */
	public function remove()
	{

	}

}