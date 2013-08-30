<?php


/**
 * Product Category controller
 *
 * @package application/controller/backend
 * @author Integry Systems
 *
 * @role category
 */
class CategoryController extends StoreManagementController
{
	public function indexAction()
	{
		Category::loadTree();


		$categories = array('children' => array($this->getRecursiveJson(Category::getRootNode()->toArray())));

		$this->set('categoryList', json_encode($categories));
		$this->set('allTabsCount', array(Category::ROOT_ID => $this->getTabCounts(Category::ROOT_ID)));
		$this->set('maxUploadSize', ini_get('upload_max_filesize'));
		$this->set('defaultCurrencyCode', $this->application->getDefaultCurrencyCode());
	}

	/**
	 * Displays category form (for creating a new category or modifying an existing one)
	 *
	 * @role !category
	 *
	 */
	public function formAction()
	{

		$this->loadLanguageFile('backend/Settings');

		$category = Category::getRootNode();
		$form = $this->buildForm($category);
		$this->set("catalogForm", $form);

		$this->set('themes', array_merge(array(''), LiveCartRenderer::getThemeList()));

		$listStyles = array();
		foreach (array('LIST', 'GRID', 'TABLE') as $style)
		{
			$listStyles[$style] = $this->translate($style);
		}
		$this->set('listStyles', array_merge(array(''), $listStyles));

		$category->getSpecification()->setFormResponse($response, $form);

	}

	public function categoryAction()
	{
		$category = Category::getInstanceByID($this->request->get('id'), true);
		$category->loadSpecification();
		$arr = $category->toArray();

		$set = $category->getRelatedRecordSet('CategoryPresentation', new ARSelectFilter());
		if ($set->size())
		{
			$arr['presentation'] = $set->get(0)->toFlatArray();
		}

		return new JSONResponse($arr);
	}

	/**
	 * Add form
	 *
	 * @role !category.create
	 *
	 * @return ActionRedirectResponse
	 */
	public function addAction()
	{
		$response = new BlockResponse();
		$this->set('form', $this->buildAddForm());
	}

	/**
	 * Creates a new category record
	 *
	 * @role !category.create
	 *
	 * @return ActionRedirectResponse
	 */
	public function createAction()
	{
		$parent = Category::getRequestInstance($this->request, 'parent');

		$categoryNode = Category::getNewInstance($parent);
		$categoryNode->loadRequestModel($this->request);
		$categoryNode->save();

		return new JSONResponse($this->getCategoryJson($categoryNode->toArray()), 'success', $this->translate('_new_category_was_successfully_created'));
	}

	/**
	 * Updates a category record
	 *
	 * @role !category.update
	 *
	 * @return ActionRedirectResponse
	 */
	public function updateAction()
	{

		$categoryNode = Category::getRequestInstance($this->request);
		$validator = $this->buildValidator($categoryNode);
		if($validator->isModelValid())
		{
			$categoryNode->loadRequestModel($this->request);
			$categoryNode->save();

			// presentation
			$instance = CategoryPresentation::getInstance($categoryNode);
			$instance->loadRequestModel($this->request, 'presentation');
			$instance->save();

			return new JSONResponse($categoryNode->toFlatArray(), 'success', $this->translate('_category_succsessfully_saved'));
		}
	}

	/**
	 * Removes node from a category
	 *
	 * @role !category.remove
	 */
	public function removeAction()
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
	public function moveAction()
	{
		$targetNode = Category::getInstanceByID((int)$this->request->get("id"));
		$parentNode = Category::getInstanceByID((int)$this->request->get("parent"));
		$next = $this->request->get("next") ? Category::getInstanceByID((int)$this->request->get("next")) : null;

		try
		{
			$targetNode->moveTo($parentNode, $next);
			Category::reindex();
			Category::recalculateProductsCount();

			return new JSONResponse(false, 'success', $this->translate('_categories_tree_was_reordered'));
		}
		catch(Exception $e)
		{
			return new JSONResponse(false, 'failure', $this->translate('_unable_to_reorder_categories_tree'));
		}
	}

	public function countTabsItemsAction()
	{
		return new JSONResponse($this->getTabCounts((int)$this->request->get('id')));
	}

	private function getTabCounts($categoryId)
	{

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

	public function branchAction()
	{
		$xmlResponse = new XMLResponse();
		$rootID = (int)$this->request->get("id", null, 1);
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
		if (!$cat)
		{
			return array();
		}

		$jscat = array('title' => $cat['name_lang'], 'id' => $cat['ID'], 'attr' => array('id' => $cat['ID']), 'state' => 'closed');
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

	public function getRecursiveJsonAction($root)
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

	public function recursivePathAction()
	{
		$root = Category::getRootNode()->toArray();

		if ($this->request->get("id") == $root['ID'])
		{
			return;
		}

		$root['children'] = array(Category::getInstanceByID((int)$this->request->get("id"), true)->getPathBranchesArray());

		return new JSONResponse($this->getRecursiveJson($root));
	}

	public function popupAction()
	{
		$this->set('categoryList', $this->getRootCategoryJson());
	}

	public function productSelectPopupAction()
	{
		return $this->popup();
	}

	public function reindexAction()
	{
		ActiveTreeNode::reindex("Category");
	}

	/**
	 * Builds a category form validator
	 *
	 * @return RequestValidator
	 */
	private function buildAddValidator()
	{
		$validator = $this->getValidator("category", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("Category name should not be empty")));

		return $validator;
	}

	/**
	 * Builds a category form instance
	 *
	 * @return Form
	 */
	private function buildAddForm()
	{
		return new Form($this->buildAddValidator());
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