<?php


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
		$instance = new self();
		$instance->keywords = $keywords;
		$instance->ip = ip2long($ipAddress));
		return $instance;
	}

	public function beforeCreate()
	{
		$f = query::query()->where('SearchLog.keywords = :SearchLog.keywords:', array('SearchLog.keywords' => $this->keywords));
		$f->andWhere('SearchLog.ip = :SearchLog.ip:', array('SearchLog.ip' => $this->ip));
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