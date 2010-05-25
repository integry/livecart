<?php

ClassLoader::import('application.model.datasync.ModelApi');

ClassLoader::import('application.model.category.Category');

/**
 * Web service access layer for Category model
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 * 
 */
class CategoryApi extends ModelApi
{
	private $root = null;

	public static function canParse(Request $request)
	{
		return parent::canParse($request, array('XmlCategoryApiReader'));
	}

	public function __construct(LiveCart $application)
	{
		$this->root = Category::getRootNode();
		parent::__construct(
			$application,
			'Category',
			array_keys($this->root->getSchema()->getFieldList()) // field names, (this line means all that are in Category model).
		);
	}
	
	// ------
	public function get()
	{
		$this->getParser()->loadDataInRequest($this->application->getRequest());
		return $this->filter(/*emptyListIsException*/ true);
	}
	
	public function create()
	{
		$parser = $this->getParser();
		$category=Category::getNewInstance($this->root);		
		$category->loadRequestData($parser->loadDataInRequest(
			$this->application->getRequest()
		));	
		$this->_attachCategoryToParentNode($category)->save();
		
		return $this->statusResponse($category->getID(), 'created');
	}

	public function update()
	{
		$parser = $this->getParser();
		$request = $this->application->getRequest();
		$parser->loadDataInRequest($request);
		$category = $this->_getCategoryById($request->get('ID'));
		$category->loadRequestData($request);
		$this->_attachCategoryToParentNode($category)->save();
		return $this->statusResponse($category->getID(), 'updated');
	}

	public function delete()
	{
		$parser = $this->getParser();
		$request = $this->application->getRequest();
		$parser->loadDataInRequest($request);
		$id = $request->get('ID');
		$this->_getCategoryById($id)->delete();

		return $this->statusResponse($id, 'deleted');
	}

	public function filter($emptyListIsException = false)
	{
		$request = $this->application->getRequest();
		$parser = $this->getParser();
		$apiFieldNames = $parser->getApiFieldNames();
		$parser->loadDataInRequest($request);
		$f = new ARSelectFilter();
		$id = $request->get('ID');
		if(intval($id) > 0) // get action
		{
			$f->mergeCondition(new EqualsCond(new ARFieldHandle('Category', 'ID'), $id));
		}
		$f->setOrder(MultiLingualObject::getLangOrderHandle(new ARFieldHandle('Category', 'name')));
		$categories = ActiveRecordModel::getRecordSetArray('Category', $f);
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		if($emptyListIsException && count($categories) == 0)
		{
			throw new Exception('Category not found');
		}
		while($category = array_shift($categories))
		{
			$xmlCategory = $response->addChild('category');
			foreach($category as $k => $v)
			{
				// if(substr($k, 0, 2) != '__' && is_string($v)) // show every string whoes key does not start with __ (like __class__)
				                                                 // or maybe
				if(in_array($k, $apiFieldNames))                 // those who are allowed fields ($this->apiFieldNames) ?
				{
					// todo: how to escape in simplexml, cdata? create cdata or what?
					$xmlCategory->addChild($k, htmlentities($v));
				}
			}
		}
		return new SimpleXMLResponse($response);
	}

	// ------ 

	private function _attachCategoryToParentNode($category)
	{
		$request =  $this->application->getRequest();
		$parentId = $request->get(Category::PARENT_NODE_FIELD_NAME);
		$id = $category->getID();
		if(intval($parentId) > 0) {
			$parentCategory = $this->_getCategoryById($parentId, /* don't throw exception, i promise, i will not change root node, honestly! */ true);
			if($id > 0)
			{
				if($id == $parentId)
				{
					throw new Exception('Parent node ID cannot be equal to node ID');
				}
				$category->moveTo($parentCategory);
			} else {
				$category->setParentNode($parentCategory);
			}
			// $request->remove(Category::PARENT_NODE_FIELD_NAME);
		}
		return $category;
	}

	private function _getCategoryById($id, $includeRootNode = false)
	{
		if($includeRootNode == false && $id == $this->root->getID())
		{
			throw new Exception('Cannot change root level category.');
		}
		
		$category = Category::getInstanceByID($id, true);
		// if id is not integer getInstanceByID() will not throw exception?
		
		if(false == ($category->getID() > 0))
		{
			throw new Exception('Bad ID field value.');
		}
		return $category;
	}
}
?>
