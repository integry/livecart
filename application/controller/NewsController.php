<?php

class NewsController extends FrontendController
{
	public function index()
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('NewsPost', 'isEnabled'), true));
		$f->setOrder(new ARFieldHandle('NewsPost', 'position'), 'DESC');
		return new ActionResponse('news', ActiveRecordModel::getRecordSetArray('NewsPost', $f));
	}	
}

?>