<?php

ClassLoader::import("application.controller.backend.abstract.ObjectImageController");
ClassLoader::import('application.model.category.Category');
ClassLoader::import("application.model.category.CategoryImage");

/**
 * Product Category Image controller
 *
 * @package application.controller.backend
 * @author Rinalds Uzkalns <rinalds@integry.net>
 *
 */
class CategoryImageController extends ObjectImageController
{
	protected function getModelClass()
	{
        return 'CategoryImage';
    }
    
	protected function getOwnerClass()
	{
        return 'Category';
    }
    
    public function index()
    {
        return parent::index();
    }
		
    public function upload()
    {
        return parent::upload();
    }

	public function save()
	{
        return parent::save();
    }
	
	public function delete()
	{
        return parent::delete();
    }
    
	/**
	 * Save currency order
	 * @return RawResponse
	 */
	public function saveOrder()
	{
	  	$categoryId = $this->request->getValue('categoryId');
	  	
		$order = $this->request->getValue('catImageList_' . $categoryId);
			
		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle('CategoryImage', 'ID'), $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet('CategoryImage', $update);  	
		}

        // set category main image
        if (isset($order[0]))
        {
            $category = Category::getInstanceByID($categoryId);
            $category->defaultImage->set(ActiveRecordModel::getInstanceByID('CategoryImage', $order[0]));
            $category->save();            
        }

		$resp = new RawResponse();
	  	$resp->setContent($this->request->getValue('draggedId'));
		return $resp;		  	
	}				
}	
	  
?>