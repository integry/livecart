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
		$this->set('news', \sitenews\NewsPost::getInstanceByID($this->dispatcher->getParam('id')));
		return;

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
		$f->orderBy(new ARFieldHandle('NewsPost', 'position'), 'DESC');

		return new ActionResponse('news', ActiveRecordModel::getRecordSetArray('NewsPost', $f));
	}

	private function addIndexBreadCrumb()
	{
		//$this->addBreadCrumb($this->translate('_news'), $this->url->get('news'), true));
	}
}

?>