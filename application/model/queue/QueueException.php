<?php
/**
 * Base Queue exception. All interactions with the queues must capture this exception,
 * and process the messages as if no queue was used in case of failure.
 *
 * @package application.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
class QueueException extends Exception
{
	public function __construct($className, $message)
	{
		parent::__construct('Exception in '.$className.': '.$message);
	}
} 