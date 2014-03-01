<?php

namespace newsletter;

/**
 * Newsletter subscriber
 *
 * @package application/model/newsletter
 * @author Integry Systems <http://integry.com>
 */
class NewsletterSubscriber extends \ActiveRecordModel
{
	public $ID;
	public $userID;
	public $isEnabled;
	public $email;
	public $confirmationCode;

	public static function getNewInstanceByUser(\user\User $user)
	{
		$instance = new self();
		$instance->userID = $user->getID();
		$instance->email = $user->email;
		return $instance;
	}

	public static function getNewInstanceByEmail($email)
	{
		$instance = new self();
		$instance->email = $email;
		return $instance;
	}

	public static function getInstanceByEmail($email)
	{
		return self::query()->where('email = :email:', array('email' => $email))->execute()->getFirst();
	}

	public function beforeCreate()
	{
		$str = '';
		for ($k = 0; $k < 20; $k++)
		{
			$str .= chr(rand(0, 255));
		}

		$this->confirmationCode = substr(md5($str), 0, 12);
	}
}

?>
