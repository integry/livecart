<?php
/**
 * Factory model used to create the queue object and return it to the object that uses the queue.
 * This way we can easy switch/remove/add new queue engines. The model gets the currently used
 * queue from the LiveCart Config object.
 *
 * @package application.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */

class QueueFactory
{
	/**
	 * Factory method that returns the currently selected queue method object.
	 * @param Config $config
	 * @param $queueName
	 * @return MysqlQueue|SemaphoreQueue|NoQueue
	 * @throws QueueException
	 */
	public static function getQueue(Config &$config, $queueName)
	{
		//Verify if name is integer, because some queues accept only integer names.
		if (!is_int($queueName)) throw new QueueException(__CLASS__, 'Queue name must be integer value');

		$queue = null;
		$class = $config->get('QUEUE_METHOD');
		ClassLoader::import("application.model.queue." . $class);

		//Try to initialize the set queue method, fallback to NoQueue
		try
		{
			if ('MysqlQueue' == $class)
			{
				$queue = MysqlQueue::getNewInstance();
			}
			else
			{
				$queue = new $class();
			}
		}
		catch(Exception $e)
		{
			ClassLoader::import("application.model.queue.NoQueue");
			$queue = new NoQueue($queueName);
		}

		$queue->setQueueName($queueName);

		return $queue;
	}

	/**
	 * Returns the currently available queue methods (in the application/method/queue folder)
	 * @param LiveCart $application
	 * @return array
	 */
	public static function getQueueMethods(LiveCart &$application)
	{
		$ret = array();

		$translationHandler = $application->getLocale()->translationManager();

		foreach (new DirectoryIterator(dirname(__file__) . '/') as $method)
		{
			if (substr($method->getFileName(), 0, 1) != '.')
			{
				$class = substr($method->getFileName(), 0, -4);

				if ((substr($class, -5) == 'Queue') && (file_exists($method->getPathname())))
				{
					include_once $method->getPathname();
					$translationHandler->setDefinition($class, $class);
					$ret[] = $class;
				}
			}
		}

		$ret = array_merge(array('NoQueue'), array_diff($ret, array('NoQueue')));

		return $ret;
	}
}