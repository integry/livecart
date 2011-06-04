<?php

ClassLoader::import("application.controller.FrontendController");
ClassLoader::import('application.model.staticpage.StaticPage');

/**
 * Displays static pages
 *
 * @author Integry Systems
 * @package application.controller
 */
class StaticPageController extends FrontendController
{
	public function view()
	{
		$this->loadLanguageFile('Frontend');
		$page = StaticPage::getInstanceByHandle($this->request->get('handle'));

		if ($parent = $page->parent->get())
		{
			while ($parent)
			{
				$parent->load();
				$urlParams = array('controller' => 'staticPage',
								   'action' => 'view',
								   'handle' => $parent->handle->get(),
								   );

				$this->addBreadCrumb($parent->getValueByLang('title'), $this->router->createUrl($urlParams, true));
				$parent = $parent->parent->get();
			}
		}

		$pageArray = $page->toArray();
		$this->addBreadCrumb($pageArray['title_lang'], '');

		$response = new ActionResponse('page', $pageArray);
		$response->set('subPages', $page->getSubPageArray());
		return $response;
	}
}

?>