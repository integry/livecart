<?php

ClassLoader::import('application.model.user.User');

/**
 * Newsletter subscriber
 *
 * @package application.model.newsletter
 * @author Integry Systems <http://integry.com>
 */
class NewsletterSubscriber extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userID", "User", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("email", ARVarchar::instance(100)));
		$schema->registerField(new ARField("confirmationCode", ARVarchar::instance(40)));
	}

	public static function getNewInstanceByUser(User $user)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->user->set($user);
		$instance->email->set($user->email->get());
		return $instance;
	}

	public static function getNewInstanceByEmail($email)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->email->set($email);
		return $instance;
	}

	public static function getInstanceByEmail($email)
	{
		$s = self::getRecordSet(__class__, new ARSelectFilter(new EqualsCond(new ARFieldHandle(__class__, 'email'), $email)));
		if ($s->size())
		{
			return $s->get(0);
		}
	}

	protected function insert()
	{
		$str = '';
		for ($k = 0; $k < 20; $k++)
		{
			$str .= chr(rand(0, 255));
		}

		$this->confirmationCode->set(substr(md5($str), 0, 12));

		return parent::insert();
	}
}

?>