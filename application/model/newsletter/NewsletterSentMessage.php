<?php

ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.newsletter.NewsletterSubscriber');
ClassLoader::import('application.model.newsletter.NewsletterMessage');

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
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("messageID", "NewsletterMessage", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userID", "User", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("subscriberID", "NewsletterSubscriber", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARField("time", ARDateTime::instance()));
	}

	public static function getNewInstanceBySubscriber(NewsletterMessage $message, NewsletterSubscriber $subscriber)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->message->set($message);
		$instance->subscriber->set($subscriber);
		return $instance;
	}

	public static function getNewInstanceByUser(NewsletterMessage $message, User $user)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->message->set($message);
		$instance->user->set($user);
		return $instance;
	}
}

?>