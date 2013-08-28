<?php


/**
 * Registers which newsletter messages have been sent to which newsletter subscribers
 *
 * @package application.model.newsletter
 * @author Integry Systems <http://integry.com>
 */
class NewsletterSentMessage extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $messageID", "NewsletterMessage", "ID", null, ARInteger::instance()));
		public $userID", "User", "ID", null, ARInteger::instance()));
		public $subscriberID", "NewsletterSubscriber", "ID", null, ARInteger::instance()));
		//public $time;
	}

	public static function getNewInstanceBySubscriber(NewsletterMessage $message, NewsletterSubscriber $subscriber)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->message = $message);
		$instance->subscriber = $subscriber);
		return $instance;
	}

	public static function getNewInstanceByUser(NewsletterMessage $message, User $user)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->message = $message);
		$instance->user = $user);
		return $instance;
	}
}

?>