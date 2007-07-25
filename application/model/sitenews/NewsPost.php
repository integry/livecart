<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * News post entry
 *
 * @package application.model.news
 * @author Integry Systems <http://integry.com>
 */
class NewsPost extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName(__CLASS__);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));	
		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
		$schema->registerField(new ARField("time", ARDateTime::instance()));
		$schema->registerField(new ARField("title", ARArray::instance()));
		$schema->registerField(new ARField("text", ARArray::instance()));
	}
}

?>