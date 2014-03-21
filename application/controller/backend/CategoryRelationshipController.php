<?php


/**
 * Controller for handling category based actions performed by store administrators
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role category
 */
class CategoryRelationshipController extends StoreManagementController
{
	public function indexAction()
	{
		$category = Category::getInstanceById($this->request->get('id'), ActiveRecord::LOAD_DATA);

		$f = select();
		$f->orderBy(f('CategoryRelationship.position'));
		$additional = $category->getRelatedRecordSet('CategoryRelationship', $f, array('Category_RelatedCategory'));
		$categories = array();
		foreach ($additional as $cat)
		{
			$categories[] = $cat;
			$cat->relatedCategory->load();
			$cat->relatedCategory->getPathNodeSet();
		}

		$this->set('category', $category->toArray());
		$this->set('categories', ARSet::buildFromArray($categories)->toArray());

	}

	public function addCategoryAction()
	{
		$category = Category::getInstanceByID($this->request->get('id'), ActiveRecord::LOAD_DATA, array('Category'));
		$relatedCategory = Category::getInstanceByID($this->request->get('categoryId'), ActiveRecord::LOAD_DATA);

		// check if the category is not assigned to this category already
		$f = select(eq('CategoryRelationship.relatedCategoryID', $relatedCategory->getID()));
		if ($category->getRelatedRecordSet('CategoryRelationship', $f)->count())
		{
			return new JSONResponse(false, 'failure', $this->translate('_err_already_assigned'));
		}

		$relation = CategoryRelationship::getNewInstance($category, $relatedCategory);
		$relation->save();

		$relatedCategory->getPathNodeSet();
		return new JSONResponse(array('data' => $relation->toFlatArray()));
	}

	public function saveOrderAction()
	{
	  	$order = $this->request->get('relatedCategories_' . $this->request->get('id'));
		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition('CategoryRelationship.ID = :CategoryRelationship.ID:', array('CategoryRelationship.ID' => $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet('CategoryRelationship', $update);
		}

		return new JSONResponse(false, 'success');
	}

	public function deleteAction()
	{
		$relation = CategoryRelationship::getInstanceByID($this->request->get('categoryId'));
		$relation->delete();

		return new JSONResponse(array('data' => $relation->toFlatArray()));
	}
}