<?php

ClassLoader::import('application.model.searchable.SearchableModel');

/**
 * Search static pages
 *
 * @package application.model.searchable
 * @author Integry Systems
 */
class SearchableConfiguration extends SearchableModel
{

	public function getClassName()
	{
		return 'SearchableItem';
	}

	public function loadClass()
	{
		ClassLoader::import('application.model.searchable.item.SearchableItem');
	}

	public function getSelectFilter($searchTerm)
	{
		$c = new ARExpressionHandle($this->getWeighedSearchCondition(array('value' => 1), $searchTerm));
		$app = ActiveRecordModel::getApplication();
		$f = new ARSelectFilter(new MoreThanCond($c, 0));
		$f->mergeCondition(
			new OrChainCondition(
				array
				(
					eq(f('SearchableItem.locale'), $app->getDefaultLanguageCode()),
					eq(f('SearchableItem.locale'), $app->getLocaleCode()),
					isnull(f('SearchableItem.locale'))
				)
			)
		);
		$f->setOrder($c, 'DESC');
		return $f;
	}

	public function isFrontend()
	{
		return false;
	}
}

?>