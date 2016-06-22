<?php
/**
 * The interface that all queue models must use. When adding new queue engines, the below methods must be implemented.
 *
 * @package application.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
interface QueueInterface
{
	/**
	 * Set the queue name
	 * @param $queueName
	 */
	public function setQueueName($queueName);

	/**
	 * Adds a message to the queue. If priority parameter is passed, it will be assigned to the message.
	 * Must throw QueueException on failure.
	 *
	 * @param $message
	 * @param $priority
	 * @throws QueueException
	 * @return mixed
	 */
	public function send($message, $priority);

	/**
	 * Consume a message from the queue. The messages with hither priority are fetched first.
	 * @return mixed
	 */
	public function receive();

	/**
	 * Delete a message from the queue upon successfully consuming the message.
	 * @return mixed
	 */
	public function remove();

}