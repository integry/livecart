<?php

ClassLoader::import('application.model.sitenews.NewsPost');

class NewsController extends FrontendController
{
	public function index()
	{
//		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('NewsPost', 'isEnabled'), true));
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('NewsPost', 'position'), 'ASC');
		return new ActionResponse('news', ActiveRecordModel::getRecordSetArray('NewsPost', $f));
	}	
}

?>