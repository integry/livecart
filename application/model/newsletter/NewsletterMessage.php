<?php


/**
 * Newsletter message
 *
 * @package application/model/newsletter
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

		public $ID;
		public $status;
		public $time;
		public $subject;
		public $text;
		public $html;
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
		$email = 'subject', $this->subject);
		$email = 'htmlMessage', $this->html);
		$email = 'text', $this->text);
		$email = 'email', $this->text);

		$email->setFrom($config->get('NEWSLETTER_EMAIL') ? $config->get('NEWSLETTER_EMAIL') : $config->get('MAIN_EMAIL'), $config->get('STORE_NAME'));

		if ($user = $sent->user)
		{
			$email->setTo($user->email, $user->getName());
			$email = 'email', $user->email);
		}
		else if ($subscriber = $sent->subscriber)
		{
			$email->setTo($subscriber->email);
			$email = 'email', $subscriber->email);
		}

		//$sent->time = new ARExpressionHandle('NOW()'));
		$sent->save();

		if ($this->status == self::STATUS_NOT_SENT)
		{
			$this->status = self::STATUS_PARTIALLY_SENT);
			$this->time = new ARExpressionHandle('NOW()'));
			$this->save();
		}

		return $email->send();
	}

	public function markAsSent()
	{
		$this->status = self::STATUS_SENT);
		$this->time = new ARExpressionHandle('NOW()'));
		$this->save();
	}
}

?>