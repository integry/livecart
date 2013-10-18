<?php

/**
 * Displays static pages
 *
 * @author Integry Systems
 * @package application/controller
 */
class StaticpageController extends ControllerBase
{
	public function viewAction($handle)
	{
		$page = \staticpage\StaticPage::query()->where('handle = :handle:')->bind(array('handle' => $handle))->execute()->getFirst();
		$this->set('page', $page);
		$this->view->pick('staticpage/view');

		/*
		$this->loadLanguageFile('Frontend');

		$page = StaticPage::getInstanceByHandle($this->request->get('handle'));

		if ($parent = $page->parent)
		{
			while ($parent)
			{
				$parent->load();
				$urlParams = array('controller' => 'staticPage',
								   'action' => 'view',
								   'handle' => $parent->handle,
								   );

				$this->addBreadCrumb($parent->getValueByLang('title'), $this->router->createUrl($urlParams, true));
				$parent = $parent->parent;
			}
		}

		$pageArray = $page->toArray();
		$this->addBreadCrumb($pageArray['title_lang'], '');

		$this->set('page', $pageArray);
		$response = 'subPages', $page->getSubPageArray());
		*/
	}
}

?>