<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.product.ProductSpecification");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @role admin.store.product
 */
class ProductController extends StoreManagementController {


	public function index()
	{
		$category = Category::getInstanceByID($this->request->getValue("id"));
		$path = $this->getCategoryPathArray($category);

		$response = new ActionResponse();
		$response->setValue("path", $path);

		$productList = $category->getProductArray();
		$response->setValue("productList", $productList);
		$response->setValue("categoryID", $this->request->getValue("id"));
		return $response;
	}

	/**
	 * Displays main product information form
	 *
	 * @return unknown
	 */
	public function add()
	{
		$category = Category::getInstanceByID($this->request->getValue("id"), ActiveRecordModel::LOAD_DATA);
		$product = Product::getNewInstance($category);
		
		return $this->productForm($product);		
	}

	private function productForm(Product $product)
	{
		$specFields = $product->getSpecificationFieldSet();
				
		$form = $this->buildForm($product);

//		$form->setData($product->toArray());
		$response = new ActionResponse();

/*
		if (!empty($productId))
		{
			$product = Product::getInstanceByID($productId, Product::LOAD_DATA);
			$category = $product->category->get();
			$specFieldArray = $category->getSpecificationFieldArray();
			$response->setValue("specFieldList", $specFieldArray);

			$productSpec = new ProductSpecification($product);
		}
*/
		$languages = array();
		foreach ($this->store->getLanguageArray() as $lang)
		{
			$languages[$lang] = $this->locale->info()->getOriginalLanguageName($lang);
		}
		$response->setValue("languageList", $languages);

		$response->setValue("productForm", $form);

		$this->setLayout("dev");
		return $response; 	
	}
	
	private function buildValidator()
	{
		$validator = new RequestValidator("productFormValidator", $this->request);
		return $validator;
	}

	private function buildForm()
	{
		ClassLoader::import("framework.request.validator.Form");

		$form = new Form($this->buildValidator());
		return $form;
	}

	/**
	 * Gets path to a current node (including current node)
	 *
	 * Overloads parent method
	 * @return array
	 */
	private function getCategoryPathArray(Category $category)
	{
		$path = array();
		$pathNodes = $category->getPathNodeSet(Category::INCLUDE_ROOT_NODE);
		$defaultLang = $this->store->getDefaultLanguageCode();

		foreach ($pathNodes as $node)
		{
			$path[] = $node->getValueByLang('name', $defaultLang);
		}
		return $path;
	}

	public function test()
	{
		$product = ActiveRecordModel::getNewInstance("Product");
		$product->category->set(ActiveTreeNode::getInstanceByID("Category", 53));

		$product->save();
	}
}

?>