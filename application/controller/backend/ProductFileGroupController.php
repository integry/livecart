<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.product.Product");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role product
 */
class ProductFileGroupController extends StoreManagementController 
{
	/**
	 * @role update
	 */
	public function create()
	{
		$product = Product::getInstanceByID((int)$this->request->get('productID'));
		$fileGroup = ProductFileGroup::getNewInstance($product);
		return $this->save($fileGroup);
	}
	
	/**
	 * @role update
	 */
	public function update()
	{
		$fileGroup = ProductFileGroup::getInstanceByID((int)$this->request->get('ID'));
		return $this->save($fileGroup);
	}
	
	private function save(ProductFileGroup $fileGroup)
	{
		$validator = $this->buildValidator();
		if ($validator->isValid())
		{   		
			foreach ($this->application->getLanguageArray(true) as $lang)
			{
				if ($this->request->isValueSet('name_' . $lang))
				{
					$fileGroup->setValueByLang('name', $lang, $this->request->get('name_' . $lang));
				}
			}
			
			$fileGroup->save();
			
			return new JSONResponse(array('status' => "success", 'ID' => $fileGroup->getID()));
		}
		else
		{
			return new JSONResponse(array('status' => "failure", 'errors' => $validator->getErrorList()));
		}
	}
	
	/**
	 * @role update
	 */
	public function delete()
	{
		ProductFileGroup::getInstanceByID((int)$this->request->get('id'))->delete();
		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function sort()
	{
		foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
		{
			if(empty($key)) continue;
			$fileGroup = ProductFileGroup::getInstanceByID((int)$key); 
			$fileGroup->position->set((int)$position);
			$fileGroup->save();
		}
		
		return new JSONResponse(false, 'success');
	}

	public function edit()
	{
		$group = ProductFileGroup::getInstanceByID((int)$this->request->get('id'), true);
		
		return new JSONResponse($group->toArray());
	}
	
	private function buildValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");
		$validator = new RequestValidator("productFileGroupValidator", $this->request);

		$validator->addCheck('name_' . $this->application->getDefaultLanguageCode(), new IsNotEmptyCheck($this->translate('_err_group_name_is_empty')));

		return $validator;
	}
	
}

?>