<?php


/**
 * Static page management
 *
 * @package application/controller/backend
 * @author	Integry Systems
 * @role page
 */
class StaticPageController extends StoreManagementController
{
	/**
	 *	Main settings page
	 */
	public function indexAction()
	{
		$f = new ARSelectFilter();
		$f->orderBy('StaticPage.position');
		$f->orderBy('StaticPage.parentID');
		$s = ActiveRecordModel::getRecordSetArray('StaticPage', $f);

		$pages = array();
		foreach ($s as $page)
		{
			$pointers[$page['ID']] = array('title' => $page['title_lang'], 'id' => $page['ID'], 'parentID' => $page['parentID']);
		}

		foreach ($pointers as $page)
		{
			if ($page['parentID'] && !empty($pointers[$page['parentID']]))
			{
				$root =& $pointers[$page['parentID']];
			}
			else
			{
				$root =& $pages;
			}

			$root['children'][] =& $pointers[$page['id']];
		}


		$this->set('pages', json_encode($pages));

		$form = $this->getForm();
		$page = StaticPage::getNewInstance();
		$page->getSpecification()->setFormResponse($response, $form);
		$this->set('form', $form);
		$this->set('page', $page->toArray());

	}

	public function editAction()
	{
		$page = StaticPage::getInstanceById($this->request->get('id'), StaticPage::LOAD_DATA);
		$page->getSpecification();
		return new JSONResponse($page->toArray());
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$page = StaticPage::getInstanceById((int)$this->request->get('id'), StaticPage::LOAD_DATA);
		return $this->save($page);
	}

	/**
	 * @role update
	 */
	public function moveAction()
	{
		$page = StaticPage::getInstanceById((int)$this->request->get('id'), StaticPage::LOAD_DATA);

		// update parent
		if ($this->request->get('parent'))
		{
			$parent = StaticPage::getInstanceById((int)$this->request->get('parent'), StaticPage::LOAD_DATA);
		}
		else
		{
			$parent = null;
		}

		$page->parent->set($parent);
		$page->save();

		// update order
		$f = new ARUpdateFilter();
		if ($parent)
		{
			$f->setCondition(eq(f('StaticPage.parentID'), $parent->getID()));
		}
		else
		{
			$f->setCondition(new IsNullCond(f('StaticPage.parentID')));
		}

		$f->addModifier('StaticPage.position', new ARExpressionHandle('position+2'));

		if ($this->request->get('previous'))
		{
			$previous = StaticPage::getInstanceById((int)$this->request->get('previous'), StaticPage::LOAD_DATA);
			$position = $previous->position;
			$f->andWhere(gt(f('StaticPage.position'), $position));
			$page->position->set($position + 1);
		}
		else
		{
			$previous = null;
			$page->position->set(1);
		}

		ActiveRecordModel::updateRecordSet('StaticPage', $f);
		$page->save();

		return new JSONResponse(array(), 'success', $this->translate('_pages_were_successfully_reordered'));
	}

	/**
	 * @role create
	 */
	public function createAction()
	{
		$page = StaticPage::getNewInstance();

		return $this->save($page);
	}

	/**
	 * @role remove
	 */
	public function deleteAction()
	{
		try
		{
			$inst = StaticPage::getInstanceById($this->request->get('id'), StaticPage::LOAD_DATA);
			$inst->delete();
			return new JSONResponse(array('id' => $inst->getID()), 'success');
		}
		catch (Exception $e)
		{
			return new JSONResponse(false, 'failure');
		}
	}

	public function emptyPageAction()
	{

	}

	public function saveAction()
	{
		$data = $this->request->getJSON();
		$page = StaticPage::getInstanceByID($data['ID'], true);
		$page->getSpecification();
		$page->loadRequestModel($this->request);

		$menu = array(
			'INFORMATION' => !empty($data['menuInformation']),
			'ROOT_CATEGORIES' => !empty($data['menuRootCategories'])
		);

		if(!array_filter($menu))
		{
			$menu = null;
		}

		$page->menu->set($menu);

		$page->save();

		return new JSONResponse(array(), 'success', $this->translate('_page_has_been_successfully_saved'));
	}

	private function getForm()
	{
		return new Form($this->buildValidator());
	}

	private function buildValidator()
	{

		$val = $this->getValidator('staticPage', $this->request);
		$val->add('title', new Validator\PresenceOf(array('message' => $this->translate('_err_title_empty'))));
		$val->add('text', new Validator\PresenceOf(array('message' => $this->translate('_err_text_empty'))));
		$val->addFilter('handle', HandleFilter::create());

		return $val;
	}
}

?>