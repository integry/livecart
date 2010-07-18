<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.newsletter.NewsletterSentMessage');
ClassLoader::import('application.model.Email');

/**
 * Newsletter message
 *
 * @package application.model.newsletter
 * @author Integry Systems <http://integry.com>
 */
class NewsletterMessage extends ActiveRecordModel
{
	const STATUS_NOT_SENT = 0;
	const STATUS_PARTIALLY_SENT = 1;
	const STATUS_SENT = 2;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("status", ARInteger::instance()));
		$schema->registerField(new ARField("time", ARDateTime::instance()));
		$schema->registerField(new ARField("subject", ARVarchar::instance(255)));
		$schema->registerField(new ARField("text", ARText::instance()));
		$schema->registerField(new ARField("html", ARText::instance()));
	}

	public function getSentCount()
	{
		return $this->getRelatedRecordCount('NewsletterSentMessage');
	}

	public function send(ActiveRecordModel $recipient, LiveCart $application)
	{
		if ($recipient instanceof User)
		{
			return $this->sendToUser($recipient, $application);
		}
		else if ($recipient instanceof NewsletterSubscriber)
		{
			return $this->sendToSubscriber($recipient, $application);
		}
		else
		{
			throw new ApplicationException('Invalid recipient type ' . get_class($recipient));
		}
	}

	public function sendToUser(User $user, LiveCart $application)
	{
		return $this->sendMessage(NewsletterSentMessage::getNewInstanceByUser($this, $user), $application);
	}

	public function sendToSubscriber(NewsletterSubscriber $subscriber, LiveCart $application)
	{
		return $this->sendMessage(NewsletterSentMessage::getNewInstanceBySubscriber($this, $subscriber), $application);
	}

	private function sendMessage(NewsletterSentMessage $sent, LiveCart $application)
	{
		$config = $application->getConfig();

		$email = new Email($application);
		$email->setTemplate('newsletter/template');
		$email->set('subject', $this->subject->get());
		$email->set('htmlMessage', $this->html->get());
		$email->set('text', $this->text->get());
		$email->set('email', $this->text->get());
		
		$email->setFrom($config->get('NEWSLETTER_EMAIL') ? $config->get('NEWSLETTER_EMAIL') : $config->get('MAIN_EMAIL'), $config->get('STORE_NAME'));

		if ($user = $sent->user->get())
		{
			$email->setTo($user->email->get(), $user->getName());
			$email->set('email', $user->email->get());
		}
		else if ($subscriber = $sent->subscriber->get())
		{
			$email->setTo($subscriber->email->get());
			$email->set('email', $subscriber->email->get());
		}

		//$sent->time->set(new ARExpressionHandle('NOW()'));
		$sent->save();

		if ($this->status->get() == self::STATUS_NOT_SENT)
		{
			$this->status->set(self::STATUS_PARTIALLY_SENT);
			$this->time->set(new ARExpressionHandle('NOW()'));
			$this->save();
		}

		return $email->send();
	}

	public function markAsSent()
	{
		$this->status->set(self::STATUS_SENT);
		$this->time->set(new ARExpressionHandle('NOW()'));
		$this->save();
	}
}

?>