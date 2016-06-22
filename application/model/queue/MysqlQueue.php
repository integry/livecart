<?php
/**
 * MysqlQueue engine model. Use to add/consume/remove messages from the MysqlQueue table in the db.
 * No need for db access setup, as it already inherits ActiveRecordModel that takes care of this.
 *
 * @package application.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.queue.QueueInterface');
ClassLoader::import('application.model.queue.QueueException');

class MysqlQueue extends ActiveRecordModel implements QueueInterface
{
	/**
	 * The id of the currently processed row.
	 * @var int
	 */
	private $id = 0;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName(__CLASS__);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARInteger::instance()));
		$schema->registerField(new ARField("message", ARText::instance(1)));
		$schema->registerField(new ARField("added", ARDateTime::instance()));
		$schema->registerField(new ARField("consumed", ARDateTime::instance()));
		$schema->registerField(new ARField("priority", ARInteger::instance()));
		$schema->registerField(new ARField("isProcessed", ARInteger::instance(1)));
	}

	public static function getNewInstance()
	{
		return ActiveRecord::getNewInstance(__CLASS__);
	}

	/**
	 * Set the queue name.
	 * @param $queueName
	 */
	public function setQueueName($queueName)
	{
		$this->name->set($queueName);
	}

	/**
	 * Adds a message to the queue. If priority parameter is passed, it will be assigned to the message.
	 * @param $message
	 * @param $priority
	 * @throws QueueException
	 * @return void
	 */
	public function send($message, $priority)
	{
		if (function_exists('gzcompress'))
		{
			$message = gzcompress($message);
		}
		$this->insertMessage($message, $priority);
	}

	/**
	 * Consume a message from the queue. The messages with hither priority are fetched first.
	 * @return mixed
	 */
	public function receive()
	{
		$startTime = time();
		$timeout = 1*60;//1 minute

		//Loop for up to one minute in order to find a message not being processed by other worker thread.
		while (time()-$startTime<$timeout)
		{
			//Get the first available row
			$firstRow = $this->db->executeQuery("SELECT * from `".__CLASS__."` where isProcessed IS NULL ORDER BY priority DESC, ID ASC LIMIT 0,1");
			//If no result, it means no more rows for processing, so break the while
			if(!$firstRow->next()) break;
			$row = $firstRow->getRow();
			$this->id = $row['ID'];
			if (!$this->id) break;

			//Update isProcessed for this row
			$this->db->executeQuery("UPDATE `".__CLASS__."` set isProcessed=1, consumed=now() where ID=".$this->id);

			//check affected rows - if 0 - start all over. It would mean that another thread is already processing this message, so we should move to the next one.
			if (!$this->db->getUpdateCount()) continue;

			if (function_exists('gzuncompress'))
			{
				$row['message'] = gzuncompress($row['message']);
			}

			return $row['message'];
		}
	}

	/**
	 * Insert the message, along with the specified priority. Write to the php error_log on failure.
	 * @param $message
	 * @throws QueueException
	 * @param $priority
	 */
	private function insertMessage($message, $priority)
	{
		$sql = 'INSERT INTO `'.__CLASS__.'` SET name=' . (int)$this->name->get() .', ' . 'message="' . addslashes($message).'", priority='.(int)$priority;
		try
		{
			$this->db->executeQuery($sql);
		}
		catch (Exception $e)
		{
			$message = "Failed to queue message: MysqlQueue->insertMessage(). QueueName=".(int)$this->name->get().'. ExceptionMe->getMessage()'.substr($e->getMessage(), 0, 150);
			error_log($message);
			throw new QueueException(__CLASS__, $message);
		}
	}

	/**
	 * Delete a message from the queue upon successfully consuming the message.
	 * @return mixed
	 */
	public function remove()
	{
		//Delete the currently processed row
		$affectedRows = $this->db->executeQuery("DELETE from `".__CLASS__."` where ID=".$this->id);
	}
}