<?php
/**
 * SemaphoreQueue queue engine. Uses the integrated PHP queue mechanism.
 * Very basic features and limited message size and queue length.
 * See the PHP docs how these can be increased.
 *
 * @package application.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
ClassLoader::import('application.model.queue.QueueInterface');
ClassLoader::import('application.model.queue.QueueException');

class SemaphoreQueue implements QueueInterface
{
	private $name;
	private $queue;
	private $msgType = NULL;
	private $maxMsgSize = 1000000;//1Mb

	/**
	 * @param $queueName
	 */
	public function setQueueName($queueName)
	{
		$this->name = $queueName;
		$this->queue =  msg_get_queue($this->name);
	}

	/**
	 * Adds a message to the queue. priority parameter is not supported by this engine.
	 *
	 * @param $message
	 * @param $priority
	 * @throws QueueException
	 * @return void
	 */
	public function send($message, $priority)
	{
		if (!msg_send($this->queue, 1, $message))
		{
			throw new QueueException(__CLASS__, var_export(msg_stat_queue($this->queue), true));
		}
	}

	/**
	 * Consume a message from the queue. The messages with hither priority are fetched first.
	 *
	 * @return mixed
	 */
	public function receive()
	{
		$message = null;
		msg_receive($this->queue, 1, $this->msgType, $this->maxMsgSize, $message);

		return $message;
	}

	public function remove()
	{
		// no alternative
	}
}