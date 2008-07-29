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
class ProductRelationshipGroupController extends StoreManagementController
{
	/**
	 * @role update
	 */
	public function create()
	{
		$product = Product::getInstanceByID((int)$this->request->get('ownerID'));
		$relationshipGroup = ProductRelationshipGroup::getNewInstance($product);

		return $this->save($relationshipGroup);
	}

	/**
	 * @role update
	 */
	public function update()
	{
		$relationshipGroup = ProductRelationshipGroup::getInstanceByID((int)$this->request->get('ID'));

		return $this->save($relationshipGroup);
	}

	/**
	 * @role update
	 */
	public function delete()
	{
		ProductRelationshipGroup::getInstanceByID((int)$this->request->get('id'))->delete();

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
			$relationship = ProductRelationshipGroup::getInstanceByID((int)$key);
			$relationship->position->set((int)$position);
			$relationship->save();
		}

		return new JSONResponse(false, 'success');
	}

	public function edit()
	{
		$group = ProductRelationshipGroup::getInstanceByID((int)$this->request->get('id'), true);

		return new JSONResponse($group->toArray());
	}

	private function buildValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");
		$validator = new RequestValidator("productRelationshipGroupValidator", $this->request);

		$validator->addCheck('name', new IsNotEmptyCheck($this->translate('_err_relationship_name_is_empty')));

		return $validator;
	}

	private function save(ProductRelationshipGroup $relationshipGroup)
	{
		$validator = $this->buildValidator();
		if ($validator->isValid())
		{
			$relationshipGroup->loadRequestData($this->request);
			$relationshipGroup->save();

			return new JSONResponse(array('ID' => $relationshipGroup->getID(), 'data' => $relationshipGroup->toArray()), 'success');
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure');
		}
	}
}

?>