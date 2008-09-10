<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.Category");

/**
 * Product Category controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 * @role category
 */
class CategoryController extends StoreManagementController
{
	public function index()
	{
		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());

		$response = new ActionResponse();
		$response->set('categoryList', $categoryList->toArray());
		$response->set('allTabsCount', array(Category::ROOT_ID => $this->getTabCounts(Category::ROOT_ID)));
		$response->set('maxUploadSize', ini_get('upload_max_filesize'));
		$response->set('defaultCurrencyCode', $this->application->getDefaultCurrencyCode());
		return $response;
	}

	/**
	 * Displays category form (for creating a new category or modifying an existing one)
	 *
	 * @role !category
	 *
	 * @return ActionResponse
	 */
	public function form()
	{
		ClassLoader::import('framework.request.validator.Form');
		ClassLoader::import('application.LiveCartRenderer');
		ClassLoader::import('application.model.presentation.CategoryPresentation');

		$category = Category::getInstanceByID($this->request->get("id"), Category::LOAD_DATA);
		$form = $this->buildForm($category);
		$response = new ActionResponse("catalogForm", $form);

		$categoryArr = $category->toArray();
		$form->setData($categoryArr);
		$response->set("categoryId", $categoryArr['ID']);

		$set = $category->getRelatedRecordSet('CategoryPresentation', new ARSelectFilter());
		if ($set->size())
		{
			$form->set('theme', $set->get(0)->getTheme());
		}

		$response->set('themes', array_merge(array(''), LiveCartRenderer::getThemeList()));

		$category->getSpecification()->setFormResponse($response, $form);

		return $response;
	}

	/**
	 * Creates a new category record
	 *
	 * @role !category.create
	 *
	 * @return ActionRedirectResponse
	 */
	public function create()
	{
		$parent = Category::getInstanceByID((int)$this->request->get("id"));

		$categoryNode = Category::getNewInstance($parent);
		$categoryNode->setValueByLang("name", $this->application->getDefaultLanguageCode(), 'dump' );
		$categoryNode->save();

		$categoryNode->setValueByLang("name", $this->application->getDefaultLanguageCode(), $this->translate("_new_category") . " " . $categoryNode->getID() );

		$categoryNode->save();

		return new JSONResponse($categoryNode->toArray(), 'success');
	}

	/**
	 * Updates a category record
	 *
	 * @role !category.update
	 *
	 * @return ActionRedirectResponse
	 */
	public function update()
	{
		ClassLoader::import('application.model.presentation.CategoryPresentation');

		$categoryNode = Category::getInstanceByID($this->request->get("id"), Category::LOAD_DATA);
		$validator = $this->buildValidator($categoryNode);
		if($validator->isValid())
		{
			$categoryNode->loadRequestData($this->request);
			$categoryNode->save();

			// presentation
			if ($theme = $this->request->get('theme'))
			{
				$instance = CategoryPresentation::getInstance($categoryNode);
				$instance->loadRequestData($this->request);
				$instance->save();
			}
			else
			{
				ActiveRecord::deleteByID('CategoryPresentation', $categoryNode->getID());
			}

			return new JSONResponse($categoryNode->toFlatArray(), 'success', $this->translate('_category_succsessfully_saved'));
		}
	}

	/**
	 * Debug method: outputs category tree structure
	 *
	 */
	public function viewTree()
	{
		$response = new RawResponse(Category::getInstanceByID(ActiveTreeNode::ROOT_ID, true)->toString());
		$response->setHeader('Content-type', 'text/plain');

		return $response;
	}

	/**
	 * Removes node from a category
	 *
	 * @role !category.remove
	 */
	public function remove()
	{
		try
		{
			Category::getInstanceByID($this->request->get("id"), true)->delete();
			return new JSONResponse(false, 'success', $this->translate('_category_was_successfully_removed'));
		}
		catch (Exception $e)
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_category'));
		}

	}

	/**
	 * Reorder category node
	 *
	 * @role !category.sort
	 */
	public function reorder()
	{
		$targetNode = Category::getInstanceByID((int)$this->request->get("id"));
		$parentNode = Category::getInstanceByID((int)$this->request->get("parentId"));

		try
		{
			if($direction = $this->request->get("direction", false))
			{
				if(ActiveTreeNode::DIRECTION_LEFT == $direction) $targetNode->moveLeft(false);
				if(ActiveTreeNode::DIRECTION_RIGHT == $direction) $targetNode->moveRight(false);
			}
			else
			{
				$targetNode->moveTo($parentNode);
			}

			return new JSONResponse(false, 'success', $this->translate('_categories_tree_was_reordered'));
		}
		catch(Exception $e)
		{
			return new JSONResponse(false, 'failure', $this->translate('_unable_to_reorder_categories_tree'));
		}

		return new JSONResponse($status);
	}

	public function countTabsItems()
	{
		return new JSONResponse($this->getTabCounts((int)$this->request->get('id')));
	}

	private function getTabCounts($categoryId)
	{
		ClassLoader::import('application.model.category.*');
	  	ClassLoader::import('application.model.filter.*');
	  	ClassLoader::import('application.model.product.*');

		$category = Category::getInstanceByID($categoryId, Category::LOAD_DATA);

		$reviewCond = new EqualsOrMoreCond(new ARFieldHandle('Category', 'lft'), $category->lft->get());
		$reviewCond->addAND(new EqualsOrLessCond(new ARFieldHandle('Category', 'rgt'), $category->rgt->get()));

		return array(
			'tabProducts' => $category->totalProductCount->get(),
			'tabFilters' => $this->getFilterCount($category),
			'tabFields' => $this->getSpecFieldCount($category),
			'tabImages' => $this->getCategoryImageCount($category),
			'tabOptions' => $category->getOptions()->getTotalRecordCount(),
			'tabRatingCategories' => ProductRatingType::getCategoryRatingTypes($category)->size(),
			'tabReviews' => ActiveRecordModel::getRecordCount('ProductReview', new ARSelectFilter($reviewCond), array('Category', 'Product')),
			'tabProductLists' => $category->getRelatedRecordCount('ProductList'),
		);
	}

	public function xmlBranch()
	{
		$xmlResponse = new XMLResponse();
		$rootID = (int)$this->request->get("id", 1);

		if(!in_array($rootID, array(Category::ROOT_ID, 0)))
		{
		   $category = Category::getInstanceByID($rootID);
		   $xmlResponse->set("rootID", $rootID);
		   $xmlResponse->set("categoryList", $category->getChildNodes(false, true)->toArray($this->application->getDefaultLanguageCode()));
		}

		return $xmlResponse;
	}

	public function xmlRecursivePath()
	{
		$xmlResponse = new XMLResponse();
		$targetID = (int)$this->request->get("id");

		try
		{
			$categoriesList = Category::getInstanceByID($targetID)->getPathBranchesArray();
			if(count($categoriesList) > 0 && isset($categoriesList['children'][0]['parent']))
			{
				$xmlResponse->set("rootID", $categoriesList['children'][0]['parent']);
				$xmlResponse->set("categoryList", $categoriesList);
			}

			$xmlResponse->set("doNotTouch", $this->request->get("doNotTouch"));
		}
		catch(Exception $e)
		{
		}

		$xmlResponse->set("targetID", $targetID);

		return $xmlResponse;
	}

	public function popup()
	{
		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());

		return new ActionResponse('categoryList', $categoryList->toArray());
	}

	public function productSelectPopup()
	{
		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());

		return new ActionResponse('categoryList', $categoryList->toArray());
	}

	public function reindex()
	{
		ActiveTreeNode::reindex("Category");
	}

	/**
	 * Builds a category form validator
	 *
	 * @return RequestValidator
	 */
	private function buildValidator(Category $category)
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("category", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("Category name should not be empty")));

		$category->getSpecification()->setValidation($validator);

		return $validator;
	}

	/**
	 * Builds a category form instance
	 *
	 * @return Form
	 */
	private function buildForm(Category $category)
	{
		return new Form($this->buildValidator($category));
	}

	private function getCategoryImageCount(Category $category)
	{
		return $category->getCategoryImagesSet()->getTotalRecordCount();
	}

	/**
	 * Count specification fields in this category
	 *
	 * @param Category $category Category active record
	 * @return integer
	 */
	private function getSpecFieldCount(Category $category)
	{
		return $category->getSpecificationFieldSet()->getTotalRecordCount();
	}

	/**
	 * Count filter groups in this category
	 *
	 * @param Category $category Category active record
	 * @return integer
	 */
	private function getFilterCount(Category $category)
	{
		return $category->getFilterGroupSet(false)->getTotalRecordCount();
	}
}

?>