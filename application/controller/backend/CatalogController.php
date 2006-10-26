<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("library.json.*");
ClassLoader::import("library.AJAX_TreeMenu.*");
ClassLoader::import("application.model.locale.Language");

/**
 * Controller for catalog (product category) related actions
 *
 * @package application.controller.backend
 * @author Denis Slaveckij <denis@integry.net>
 *
 */
class CatalogController extends StoreManagementController
{

	private $multiLanguage = 'en';
	
	/**
	 * This function is executed every time the controller is being loaded
	 */
	public function init()
	{
		parent::init();

        // $this->addBlock("NAV", "treeSection");
		$this->setLayout("categoryManager");

		@session_start();

		//js sources
		$app = Application::getInstance();
	}

	/**
	 * Index
	 */
	public function index()
	{
		ClassLoader::import("framework.request.validator.Form");
				
		$response = new ActionResponse();
		$response->setValue("catalogForm", $this->createCatalogForm());
		return $response;
	}
	
	/**
	 * Add catalog form
	 */
	public function add()
	{		
		$response = new ActionResponse();
		$response->setValue("catalogForm", $this->createCatalogForm());
		
		return $response;
	}
	
	/**
	 * Update catalog form
	 */
	public function update()
	{
		if($id = $this->request->getValue('id', false))
		{			
			$response = new ActionResponse();
			$response->setValue("catalogForm", $this->createCatalogForm());
			$response->setValue('id', $id);
			
			return $response;
		}
		else 
		{
			return new ActionRedirectResponse($this->request->getControllerName(), "index");
		}
	}		

	/**
	 * Saves main details of current catalog
	 * 
	 * @return ActionRedirectResponse
	 */
	public function save()
	{
		if ($id = $this->request->getValue("id", false))
		{
			return $this->updateCatalog($id);
		} 
		else 
		{
			return $this->createNewCatalog();
		}
	}

	/**
	 * Deletes catalog group.
	 * 
	 * @return ActionRecirectResponse
	 */
	public function delete()
	{
		if($id = $this->request->getValue('id', false))
		{
			TreeCatalog::delete('TreeCatalog', $id);
		}
		
		return new ActionRedirectResponse($this->request->getControllerName(), "index");
	}
	
	/**
	 * Moves category to another parent
	 * 
	 * @return ActionRedirectResponse
	 */
	public function move()
	{
		Tree::modifyTreeParent("Catalog", $this->request->getValue("id"), $this->request->getValue("moveto"));
		return new ActionRedirectResponse($this->request->getControllerName(), "index", array("id" => $this->request->getValue("moveto")));
	}
		
	/**
	 * Form of movieng node to another.
	 * @return ActionResponse
	 */
	public function moveForm()
	{
		if ($id = $this->request->getValue("id", false))
		{
			// resposne
			// $response->setValue("group", $this->createTreeHTML("formatTreeMenuForMove", "objTreeMenuAjax_catalogMove", "objTreeMenuAjax_catalogMove")->toHtml("objTreeMenuAjax_catalogMove"));

			$response = new ActionResponse();
			$response->setValue("id", $this->request->getValue("id"));
			
			return $response;
		}
		else 
		{
			die();
		}
	}	
	
	
	
	
	/**
	 * Save catalog (create new one)
	 * 
	 * @return ActionRedirectResponce
	 */
	private function createNewCatalog()
	{
		// Get catalog parrent or false (then root catalog will be used as a parent)
		$parent = $this->request->getValue('parent', false);
		
		if ($this->createCatalogForm()->getValidator()->isValid())
		{
			$catalog = TreeCatalog::getNewTreeInstance($parent);
						
			$catalog->lang($this->multiLanguage)->name->set($this->request->getValue("name"));
			$catalog->lang($this->multiLanguage)->description->set($this->request->getValue("description"));
			
			$catalog->save();
			
			return new ActionRedirectResponse($this->request->getControllerName(), "index");
		}
		else
		{			
			return new ActionRedirectResponse($this->request->getControllerName(), "add", array("parent" => $parent));
		}
	}
	
	/**
	 * Save catalog (update an existing catalog)
	 * 
	 * @return ActionRedirectResponce
	 */
	private function updateCatalog($id)
	{		
		if ($this->createCatalogForm()->getValidator()->isValid())
		{
			$catalog = Catalog::getInstanceById($id);
			
			$catalog->lang($this->multiLanguage)->name->set($this->request->getValue("name"));
			$catalog->lang($this->multiLanguage)->description->set($this->request->getValue("description"));
			
			$catalog->save();
			
			return new ActionRedirectResponse($this->request->getControllerName(), "index");
		}
		else
		{
			return new ActionRedirectResponse($this->request->getControllerName(), "update", array("id" => $id));
		}

	}
	
	/**
	 * Creates form object and defines validation rules
	 * 
	 * @return Form
	 */
	private function createCatalogForm()
	{
		ClassLoader::import("framework.request.validator.*");
		
		$validator = new RequestValidator("catalogForm", $this->request);
		$validator->addCheck("name", new MinLengthCheck($this->translate("Name must be at least two chars length"), 2));
		
		$form = new Form($validator);
		
		if ($this->request->isValueSet("id"))
		{
			$data = ActiveRecord::getInstanceById('CatalogLangData', array('catalogID' => $this->request->getValue("id"), 'languageID' => $this->multiLanguage), true)->toArray();
				
			$form->setData(array(
				'name' => $data['name'],
				'description' => 'description'
			));
		} 
		else if($this->request->isValueSet('parent')) 
		{
			$form->setValue('parent', $this->request->getValue('parent'));
		}
	
		return $form; 
	}
	
	

	
	

	

	
	/**
	 * Block of group tree.
	 */
	protected function treeSectionBlock()
	{
		//response
		$response = new BlockResponse();
		$response->setValue("title", $this->translate("_catalogTree"));
		$response->setValue("group", $this->createTreeHTML("formatTreeMenu", "objTreeMenuAjax_catalog", "objTreeMenuAjax_catalog")->toHtml());
		$response->setValue("id", $this->request->getValue("id"));

		//js sources
		$app = Application::getInstance();
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/document.js");
		
		//ajaxtreemenu js sources
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/ajax.js");
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/TreeMenuAjax.js");

		return $response;
	}

	/**
	 * Create html treemenu of catalogs.
	 * @param string $js_object_name
	 * @param string $method
	 * @return AJAX_TreeMenu_DHTML
	 */
	private function createTreeHTML($format_method, $js_object_name, $method)
	{
//		ClassLoader::import("library.AJAX_TreeMenu.*");
		
//		$tree = TreeCatalog::getAllTree("TreeCatalog", true);
		
		//format TreeMenu
//		$treemenu = new AJAX_TreeMenu();
//		$this->$format_method($treemenu, $tree, $js_object_name, $method);

		//html
		
//		return new RawResponse("OK");
//		return new AJAX_TreeMenu_DHTML("", $js_object_name, $treemenu, array('images' => Router::getInstance()->getBaseDir().'/library/AJAX_TreeMenu/imagesAlt2', 'defaultClass' => 'treeMenuDefault'));
	}



	/**
	 * Formats AJAX_TreeMenu object $treemenu.
	 * @param Ajax_TreeMenu $treemenu Formated object.
	 * @param Tree $tree
	 * @param string $js_object_name
	 * @param string $method
	 */
	private function formatTreeMenu($treemenu, $tree, $js_object_name, $method)
	{
		$node = &new AJAX_TreeNode($tree->getId(), $this->$method($tree, $js_object_name));
		$treemenu->addItem($node);
		foreach($tree as $child)
		{
			$this->formatTreeMenu($node, $child, $js_object_name, $method);
		}
	}

	/**
	 * Formats AJAX_TreeMenu object of $treemenu (form move tree).
	 * @param Ajax_TreeMenu $treemenu Formated object.
	 * @param Tree $tree
	 * @param string $js_object_name
	 * @param string $method
	 */
	private function formatTreeMenuForMove($treemenu, $tree, $js_object_name, $method)
	{
		$node = &new AJAX_TreeNode($tree->getId(), $this->$method($tree, $js_object_name));
		$treemenu->addItem($node);
		if ($tree->getId() != $this->request->getValue("id"))
		{
			foreach($tree as $child)
			{
				$this->formatTreeMenuForMove($node, $child, $js_object_name, $method);
			}
		}
	}

	/**
	 */
	private function objTreeMenuAjax_catalog($tree, $js_object_name)
	{
		$array = array();
		$array['text'] = $tree->hasId() ? $tree->lang("en")->name->get(): "ALL";
		$array['link'] = Router::getInstance()->createUrl(array("controller" => $this->request->getControllerName(), "action" => "index", "id" => $tree->getId()));
		$array['cssClass'] = (!$tree->hasId() && !$this->request->isValueSet("id")) || ($tree->hasId() && $tree->getId() == $this->request->getValue("id")) ? 'treeMenuNodeSelected' : 'treeMenuNode';
		return $array;
	}

	/**
	 */
	private function objTreeMenuAjax_catalogMove($tree, $js_object_name)
	{
		$array = array();
		$array['text'] = $tree->hasId() ? $tree->lang("en")->name->get(): "ALL";
		if ($tree->getId() != $this->request->getValue("id") && $tree->parent->get() != $this->request->getValue("id"))
		{
			$array['link'] = "javascript: makeMove(".$tree->getId().");";
		}
		$array['cssClass'] = 'treeMenuNode';
		return $array;
	}








	/**
	 * Changes current multi language.
	 * @return ActionRedirectResponse
	 */
	public function change()
	{
		@session_start();
		$_SESSION['CURRENT_multiLanguage'] = $this->request->getValue("multiLanguage");

		return new ActionRedirectResponse($this->request->getControllerName(), "index", array("id" => $this->request->getValue("id")));
	}

	public function fields()
	{
		$response = new ActionResponse();
		$response->setValue("action", "fields");
		return $response;
	}

	public function filters()
	{
		$response = new ActionResponse();
		$response->setValue("action", "filters");
		return $response;
	}
}

?>