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

		$f = query::query()->where('NewsPost.ID = :NewsPost.ID:', array('NewsPost.ID' => $this->request->get('id')));
		$f->andWhere('NewsPost.isEnabled = :NewsPost.isEnabled:', array('NewsPost.isEnabled' => true));

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

		$f = query::query()->where('NewsPost.isEnabled = :NewsPost.isEnabled:', array('NewsPost.isEnabled' => true));
		$f->orderBy('NewsPost.position', 'DESC');

		return new ActionResponse('news', ActiveRecordModel::getRecordSetArray('NewsPost', $f));
	}

	private function addIndexBreadCrumb()
	{
		//$this->addBreadCrumb($this->translate('_news'), $this->url->get('news'), true));
	}
}

?>