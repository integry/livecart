<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.Category");

ClassLoader::import("application.model.product.Product");

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
		$response = new ActionResponse();
		$response->set('categoryList', $this->getRootCategoryJson());
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
		ClassLoader::import('application.LiveCartRenderer');
		ClassLoader::import('application.model.presentation.CategoryPresentation');

		$this->loadLanguageFile('backend/Settings');

		$category = Category::getInstanceByID($this->request->get("id"), Category::LOAD_DATA);
		$form = $this->buildForm($category);
		$response = new ActionResponse("catalogForm", $form);

		$categoryArr = $category->toArray();
		$form->setData($categoryArr);
		$response->set("categoryId", $categoryArr['ID']);

		$set = $category->getRelatedRecordSet('CategoryPresentation', new ARSelectFilter());
		if ($set->size())
		{
			$form->setData($set->get(0)->toFlatArray());
		}

		$response->set('themes', array_merge(array(''), LiveCartRenderer::getThemeList()));

		$listStyles = array();
		foreach (array('LIST', 'GRID', 'TABLE') as $style)
		{
			$listStyles[$style] = $this->translate($style);
		}
		$response->set('listStyles', array_merge(array(''), $listStyles));

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
			$instance = CategoryPresentation::getInstance($categoryNode);
			$instance->loadRequestData($this->request);
			$instance->save();

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
			$categoryID = $this->request->get("id");
			$confirmed = $this->request->get("confirmed");
			$category = Category::getInstanceByID($categoryID, true);

			if($category->getActiveProductCount())
			{
				if(!$confirmed)
				{
					return new JSONResponse(
						array(
							'confirmMessage' => $this->translate('_confirm_remove_category_with_products'),
							'url' => $this->application->getRouter()->createUrl(array('controller' => 'backend.category', 'action' => 'remove', 'id' => $categoryID,'query' => array('confirmed'=>1)))
						),
						'confirm'
					);
				}
				// merge categoryID with child category IDs
				$categoryIDs = array_merge(
					Category::getRecordSet($category->getBranchFilter())->getRecordIDs(),
					array($categoryID)
				);
				// all products under category that has additional categories
				$products = ActiveRecord::getRecordSet('Product',
					select(new AndChainCondition(array(
						IN(f('Product.categoryID'), $categoryIDs),
						new RegexpCond(f('Product.categoryIntervalCache'), '[0-9]+\-[0-9]+\,[0-9]+\-[0-9]+')) // categoryIntervalCache can end with ,
					))
				);
				// move to aditional category, that is not in categoryIDs
				foreach($products as $product)
				{
					$chunks = explode(',',$product->categoryIntervalCache->get());
					while($pair = array_shift($chunks))
					{
						$sequence = explode('-',$pair);
						if(!is_array($sequence) || count($sequence) != 2  || !is_numeric($sequence[0]) || !is_numeric($sequence[1]))
						{
							continue;
						}
						$categorySet = Category::getRecordSet(select(
							new AndChainCondition(array(
								eq(f('Category.lft'), $sequence[0]),
								eq(f('Category.rgt'), $sequence[1])))));
						if($categorySet->size() != 1)
						{
							continue;
						}
						$categoryToMove = $categorySet->shift();
						if(in_array($categoryToMove->getID(), $categoryIDs))
						{
							// child category also will be removed. cant move here
							continue;
						}
						$product->categoryID->set($categoryToMove);
						$product->save();
						break; // product moved, don't care about other aditional categories.
					}
				}
			}
			// and delete category.
			$category->delete();

			Category::recalculateProductsCount();

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

			Category::reindex();
			Category::recalculateProductsCount();

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

	public function branch()
	{
		$xmlResponse = new XMLResponse();
		$rootID = (int)$this->request->get("id", 1);
		$category = Category::getInstanceByID($rootID, true);

		return new JSONResponse($this->getCategoryChildrenJson($category));
	}

	protected function getRootCategoryJson()
	{
		$root = Category::getRootNode();
		$categories = array('data' => $root->getValueByLang('name'), 'id' => $root->getID(), 'attr' => array('id' => $root->getID()), 'children' => array());
		$categories['children'] = $this->getCategoryChildrenJson($root);

		return $categories;
	}

	protected function getCategoryChildrenJson(Category $category)
	{
		$children = array();
		foreach ($category->getChildNodeArray(false, true) as $cat)
		{
			$children[] = $this->getCategoryJson($cat);
		}

		return $children;
	}

	protected function getCategoryJson($cat)
	{
		$jscat = array('data' => $cat['name_lang'], 'id' => $cat['ID'], 'attr' => array('id' => $cat['ID']), 'state' => 'closed');
		if ($cat['rgt'] - $cat['lft'] == 1)
		{
			$jscat['children'] = null;
			$jscat['state'] = 'leaf';
		}

		if (!$cat['isEnabled'])
		{
			$jscat['attr']['class'] = 'ui-state-disabled';
		}

		return $jscat;
	}

	public function getRecursiveJson($root)
	{
		$jscat = $this->getCategoryJson($root);

		if (!empty($root['children']) && is_array($root['children']))
		{
			foreach ($root['children'] as $cat)
			{
				$jscat['children'][] = $this->getRecursiveJson($cat);
			}
		}

		return $jscat;
	}

	public function recursivePath()
	{
		$root = Category::getRootNode()->toArray();
		$root['children'] = array(Category::getInstanceByID((int)$this->request->get("id"), true)->getPathBranchesArray());

		return new JSONResponse($this->getRecursiveJson($root));
	}

	public function popup()
	{
		return new ActionResponse('categoryList', $this->getRootCategoryJson());
	}

	public function productSelectPopup()
	{
		return $this->popup();
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
		$validator = $this->getValidator("category", $this->request);
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