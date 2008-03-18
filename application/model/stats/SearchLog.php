<?php

ClassLoader::import('application.model.ActiveRecordModel');

class SearchLog extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ArInteger::instance()));
		$schema->registerField(new ARField("keywords", ArVarchar::instance(60)));
		$schema->registerField(new ARField("ip", ArInteger::instance()));
		$schema->registerField(new ARField("time", ArDateTime::instance()));
	}

	public static function getNewInstance($keywords, $ipAddress)
	{
		$instance = parent::getNewInstance(__class__);
		$instance->keywords->set($keywords);
		$instance->ip->set(ip2long($ipAddress));
		return $instance;
	}

	protected function insert()
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('SearchLog', 'keywords'), $this->keywords->get()));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('SearchLog', 'ip'), $this->ip->get()));
		if (!ActiveRecordModel::getRecordCount(__class__, $f))
		{
			parent::insert();

			$update = new ARUpdateFilter();
			$update->addModifier('time', new ARExpressionHandle('NOW()'));
			$update->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'ID'), $this->getID()));
			ActiveRecordModel::updateRecordSet(__CLASS__, $update);
		}
	}
}

?>