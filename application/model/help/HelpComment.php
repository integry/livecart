<?php

class HelpComment extends ActiveRecordModel
{
    /**
     * Define table schema
     */
	public static function defineSchema()
	{
		$schema = self::getSchemaInstance(__CLASS__);
		$schema->setName(__CLASS__);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("topicID", ARVarChar::instance(100)));
		$schema->registerField(new ARField("username", ARVarChar::instance(100)));
		$schema->registerField(new ARField("text", ARText::instance()));
		$schema->registerField(new ARField("timeAdded", ARDateTime::instance()));
	}
	
	public static function getNewInstance($topicId)
	{
		// verify that the help topic exists
		$root = HelpTopic::getRootTopic();
		$topic = $root->getTopic($topicId);
		if (!$topic)
		{
			throw new ApplicationException('Help topic ' . $topicId . ' was not found!');
		}
		
		$comment = parent::getNewInstance(__CLASS__);
		$comment->topicID->set($topicId);

		return $comment;		
	}
	
	protected function insert()
	{		
		parent::insert();
		$update = new ARUpdateFilter();
		$update->addModifier('timeAdded', new ARExpressionHandle('NOW()'));
		$update->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'ID'), $this->getID()));
		ActiveRecordModel::updateRecordSet(__CLASS__, $update);
	}		
}

?>