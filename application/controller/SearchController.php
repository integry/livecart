<?php


/**
 * Search for non-products
 *
 * @author Integry Systems
 * @package application/controller
 */
class SearchController extends FrontendController
{
	public function indexAction()
	{
		$this->loadLanguageFile('Category');

		$inst = SearchableModel::getInstanceByModelClass($this->request->get('type'));
		if (!$inst)
		{
			return null;
		}

		$query = $this->request->get('q');
		$f = $inst->getSelectFilter($query);

		$perPage = $this->config->get('SEARCH_MODEL_PER_PAGE');
		$page = $this->request->get('page', 1);
		$f->setLimit($perPage, $perPage * ($page - 1));


		$this->set('results', $this->fetchData($inst, $f));
		$this->set('page', $page);
		$this->set('query', $query);
		$this->set('perPage', $perPage);
		$this->set('url', $this->url->get('search/index', 'query' => array('type' => $inst->getClassName(), 'q' => $query, 'page' => '0'))));
	}

	public function searchAllAction($searchTerm)
	{
		$res = array();

		foreach (SearchableModel::getInstances(SearchableModel::FRONTEND_SEARCH_MODEL) as $searchable)
		{
			$f = $searchable->getSelectFilter($searchTerm);
			$f->setLimit($this->config->get('SEARCH_MODEL_PREVIEW'));
			$res[$searchable->getClassName()] = $this->fetchData($searchable, $f);
		}

		return $res;
	}

	private function fetchData(SearchableModel $searchable, ARSelectFilter $filter)
	{
		$class = $searchable->getClassName();
		$ret = array();
		$ret['records'] = ActiveRecordModel::getRecordSetArray($class, $filter);
		$ret['count'] = ActiveRecordModel::getRecordCount($class, $filter);
		$ret['meta'] = $searchable->toArray();

		return $ret;
	}
}

?>