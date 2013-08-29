<?php

/**
 * Displays static pages
 *
 * @author Integry Systems
 * @package application/controller
 */
class StaticpageController extends ControllerBase
{
	public function viewActionAction()
	{
		$pages = \staticpage\StaticPage::query()->execute();
		//var_dump($pages->toArray());
		$this->view->setRenderLevel(\Phalcon\MVC\View::LEVEL_ACTION_VIEW);
		$this->view->pages = $pages->toArray();
		$this->view->pick('staticpage/view');

		$this->config->get('STORE_NAME');

		/*
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
		$response = 'subPages', $page->getSubPageArray());
		return $response;
		*/
	}
}

?>