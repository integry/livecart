<?php


/**
 * Site news controller
 *
 * @author Integry Systems
 * @package application/controller
 */	
class NewsController extends FrontendController
{
	public function viewAction()
	{	   		
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('NewsPost', 'ID'), $this->request->get('id')));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('NewsPost', 'isEnabled'), true));
		
		$s = ActiveRecordModel::getRecordSet('NewsPost', $f);
		if (!$s->size())
		{
			throw new ARNotFoundException('NewsPost', $this->request->get('id'));
		}

		$newsPost = $s->get(0)->toArray();
		
		$this->addIndexBreadCrumb();
		$this->addBreadCrumb($newsPost['title_lang'], '');

		$this->set('news', $newsPost);
	}	
	
	public function indexAction()
	{
		$this->addIndexBreadCrumb();
		
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('NewsPost', 'isEnabled'), true));
		$f->setOrder(new ARFieldHandle('NewsPost', 'position'), 'DESC');
		
		return new ActionResponse('news', ActiveRecordModel::getRecordSetArray('NewsPost', $f));
	}
	
	private function addIndexBreadCrumb()
	{
		$this->addBreadCrumb($this->translate('_news'), $this->router->createUrl(array('controller' => 'news'), true));
	}
}

?>