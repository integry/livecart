<?php

ClassLoader::import('application.model.ActiveRecordModel');

class SearchLog extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{


		public $ID;
		public $keywords;
		public $ip;
		public $time;
	}

	public static function getNewInstance($keywords, $ipAddress)
	{
		$instance = parent::getNewInstance(__class__);
		$instance->keywords = $keywords);
		$instance->ip = ip2long($ipAddress));
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