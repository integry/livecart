<?php

/**
 * Currency model
 *
 * @package application.model
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
class Currency extends ActiveRecord
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Currency");

		$schema->registerField(new ARPrimaryKeyField("ID", ArChar::instance(3)));

		$schema->registerField(new ARField("rate", ArFloat::instance(16)));
		$schema->registerField(new ARField("lastUpdated", ArDateTime::instance()));
		$schema->registerField(new ARField("isDefault", ArBool::instance()));
		$schema->registerField(new ARField("isEnabled", ArBool::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
	}

	public function setAsDefault($default = true)
	{
	  	$this->isDefault->set((bool)$default);
	}

	public function isDefault()
	{
	  	return $this->isDefault->get();
	}
}

?>
