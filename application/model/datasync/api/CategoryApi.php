<?php

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.datasync.api.reader.XmlCategoryApiReader');
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
		if(XmlCategoryApiReader::canParse($request))
		{
			return true;
		}
		return false;
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

	private function _getCategoryById($id)
	{
		if($id == $this->root->getID())
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
	
	public function delete()
	{
		$parser = $this->getParser();
		$request = $this->application->getRequest();
		$parser->loadDataInRequest($request);

		$this->_getCategoryById($request->get('ID'))->delete();
	}
	
	
	public function update()
	{
		$parser = $this->getParser();
		$request = $this->application->getRequest();
		$parser->loadDataInRequest($request);
		$category = $this->_getCategoryById($request->get('ID'));
		$category->loadRequestData($request);
		$category->save();
	}
	
	
	public function create()
	{
		$parser = $this->getParser();
		$category=Category::getNewInstance($this->root);		
		$category->loadRequestData($parser->loadDataInRequest(
			$this->application->getRequest()
		));	
		$category->save();
	}
	
	public function filter()
	{
		$request = $this->application->getRequest();
		$parser = $this->getParser();
		$apiFieldNames = $parser->getApiFieldNames();
		$parser->loadDataInRequest($request);

		$f = new ARSelectFilter();
//		$f->mergeCondition(new NotEqualsCond(new ARFieldHandle('Category', 'ID'), $this->root->getID()));

		// get action
		$id = $request->get('ID');
		if(intval($id) > 0)
		{
			$f->mergeCondition(new EqualsCond(new ARFieldHandle('Category', 'ID'), $id));
		}
		
		$f->setOrder(MultiLingualObject::getLangOrderHandle(new ARFieldHandle('Category', 'name')));
	
		$categories = ActiveRecordModel::getRecordSetArray('Category', $f);
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
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
}
?>
