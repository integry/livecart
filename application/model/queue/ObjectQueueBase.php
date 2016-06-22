<?php
/**
 * Base model for creating queues to store custom objects
 *
 * @package application.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
ClassLoader::import('application.model.queue.QueueFactory');

/**
 * Base class for communicating with the configured queue to send and receive objects from the queue.
 *
 * Class ObjectQueue
 */
class ObjectQueueBase
{
	/**
	 * Queue instance. It could be any supported type from @package application.model.queue
	 *
	 * @var MysqlQueue|NoQueue|SemaphoreQueue
	 */
	private $queue;

	/**
	 * The constructor. Sets the queue name, and builds the queue.
	 *
	 * @param Config $config
	 * @param String $queueName
	 */
	public function __construct(Config &$config, $queueName)
	{
		$this->queueName = $queueName;
		$this->queue = QueueFactory::getQueue($config, $this->queueName);
	}

	/**
	 * Sends a message to the queue. If priority is sent, the message with higher priority will be consumed first.
	 * @param $object
	 * @param $priority
	 */
	public function send($object, $priority)
	{
		$this->queue->send(serialize($object), $priority);
	}

	/**
	 * Consumes a message from the queue.
	 *
	 * @return mixed
	 */
	public function receive()
	{
		$message = $this->queue->receive();
		return unserialize($message);
	}

	/**
	 * Use this to delete the message from the queue upon successfully processing the message.
	 */
	public function remove()
	{
		$this->queue->remove();
	}

} 