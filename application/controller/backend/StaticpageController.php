<?php

use staticpage\StaticPage;
use Phalcon\Validation\Validator;

/**
 * Static page management
 *
 * @package application/controller/backend
 * @author	Integry Systems
 * @role page
 */
class StaticpageController extends ControllerBackend
{
	/**
	 *	Main settings page
	 */
	public function indexAction()
	{
		$f = StaticPage::query();
		$f->orderBy('position, parentID');
		$s = $f->execute()->toArray();

		$pages = array('children' => array());
		$pointers = array();
		foreach ((array)$s as $page)
		{
			$pointers[$page['ID']] = array('title' => $page['title'], 'id' => $page['ID'], 'parentID' => $page['parentID']);
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

		$this->set('pages', $pages);

		$this->setValidator($this->buildValidator());

		//$page = StaticPage::getNewInstance();
		//$page->getSpecification()->setFormResponse($response, $form);

		//$this->set('page', $page->toArray());
	}

	public function editAction()
	{
		$page = StaticPage::getInstanceById($this->request->get('id'));
		$page->getSpecification();
		echo json_encode($page->toArray());
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$page = StaticPage::getInstanceById((int)$this->request->getJson('id'));
		return $this->save($page);
	}

	/**
	 * @role update
	 */
	public function moveAction()
	{
		$page = StaticPage::getInstanceById((int)$this->request->get('id'));

		// update parent
		if ($this->request->get('parent'))
		{
			$parent = (int)$this->request->get('parent');
		}
		else
		{
			$parent = null;
		}

		$page->parentID = $parent;
		$page->save();

		// update order
		$sql = "UPDATE \staticpage\StaticPage SET position=position+2 WHERE ";
		if ($parent)
		{
			$sql .= "parentID=" . $parent;
		}
		else
		{
			$sql .= "parentID IS NULL";
		}

		if ($this->request->get('previous'))
		{
			$previous = StaticPage::getInstanceById((int)$this->request->get('previous'));
			$position = $previous->position;
			$sql .= " AND position>" . $position;
			$page->position = $position + 1;
		}
		else
		{
			$previous = null;
			$page->position = 1;
		}

		$this->modelsManager->executeQuery($sql);
		
		$page->save();

		echo json_encode(array('status' => 'success'));
	}

	/**
	 * @role remove
	 */
	public function deleteAction()
	{
		try
		{
			$inst = StaticPage::getInstanceById($this->request->get('id'));
			$inst->delete();
			echo json_encode(array('id' => $inst->getID(), 'status' => 'success'));
		}
		catch (Exception $e)
		{
			echo json_encode(array('status' => 'failure'));
		}
	}

	public function saveAction()
	{
		$data = $this->request->getJsonRawBody();

		if (empty($data['ID']))
		{
			$page = StaticPage::getNewInstance();
		}
		else
		{
			$page = StaticPage::getInstanceByID($data['ID']);
			$page->getSpecification();
		}
		
		$page->loadRequestData($this->request);

		$menu = array(
			'INFORMATION' => !empty($data['menuInformation']),
			'ROOT_CATEGORIES' => !empty($data['menuRootCategories'])
		);

		if(!array_filter($menu))
		{
			$menu = null;
		}

		$page->menu = $menu;

		$page->save();

		echo json_encode($page->toArray());
	}

	private function buildValidator()
	{
		$val = $this->getValidator('staticPage', $this->request);
		$val->add('title', new Validator\PresenceOf(array('message' => $this->translate('_err_title_empty'))));
		$val->add('text', new Validator\PresenceOf(array('message' => $this->translate('_err_text_empty'))));
		//$val->addFilter('handle', HandleFilter::create());

		return $val;
	}
}

?>
