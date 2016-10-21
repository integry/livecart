<?php
/**
 * EmailQueue that takes care of serializing and unserializing the Email object to and from the queue
 *
 * @package application.model.email
 * @author Shumoapp <http://shumoapp.com>
 */
ClassLoader::import('application.model.queue.QueueFactory');
ClassLoader::import('application.model.queue.ObjectQueueBase');
ClassLoader::import('application.model.Email');

ClassLoader::ignoreMissingClasses();
ClassLoader::import('library.swiftmailer.lib.swift_required', true);
ClassLoader::ignoreMissingClasses(false);

/**
 * This class communicates with the configured queue to send and receive emails from the queue.
 *
 * Class EmailQueue
 */
class EmailQueue extends ObjectQueueBase
{
	/**
	 * The constructor. Sets the queue name (244566 in this case), and builds the queue.
	 *
	 * @param Config $config
	 */
	public function __construct(Config &$config)
	{
		parent::__construct($config, 244566);
	}
} 