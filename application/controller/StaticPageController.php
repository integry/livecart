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
		$page = StaticPage::getInstanceByHandle($this->request->get('handle'))->toArray();
		$this->addBreadCrumb($page['title_lang'], '');

		return new ActionResponse('page', $page);
	}
}

?>