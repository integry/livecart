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
class ProductRelationshipController extends StoreManagementController
{
	public function index()
	{
		$productID = (int)$this->request->get('id');
		$product = Product::getInstanceByID($productID, ActiveRecord::LOAD_DATA, array('Category'));

		$languages = array();
		foreach($this->application->getLanguageList()->toArray() as $language) $languages[$language['ID']] = $language;

		$response = new ActionResponse();

		$response->set('categoryID', $product->category->get()->getID());
		$response->set('languages', $languages);
		$response->set('productID', $productID);
		$response->set('relationships', $product->getRelationships()->toArray());
		$response->set('relationshipsWithGroups', $product->getRelatedProductsWithGroupsArray());

		return $response;
	}

	/**
	 * Products popup
	 *
	 * @role update
	 */
	public function selectProduct()
	{
		$response = new ActionResponse();

		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());
		$response->set("categoryList", $categoryList->toArray($this->application->getDefaultLanguageCode()));

		return $response;
	}

	/**
	 * Creates new relationship
	 *
	 * @role update
	 */
	public function addRelated()
	{
		$productID = (int)$this->request->get('id');
		$relatedProductID = (int)$this->request->get('relatedProductID');

		$relatedProduct = Product::getInstanceByID($relatedProductID, true, array('DefaultImage' => 'ProductImage'), Product::LOAD_DATA);
		$product = Product::getInstanceByID($productID, Product::LOAD_DATA);

		if(!$relatedProduct->isRelatedTo($product))
		{
			try
			{
				$product->addRelatedProduct($relatedProduct);
				$product->save();

				$response = new ActionResponse();
				$response->set('product', $relatedProduct->toArray());
				$response->set('added', true);
				return $response;
			}
			catch(ProductRelationshipException $e)
			{
				$error = '_err_circular';
			}
		}
		else
		{
			$error = '_err_multiple';
		}

		return new JSONResponse(array('error' => $this->translate($error)));
	}

	/**
	 * @role update
	 */
	public function delete()
	{
		$productID = (int)$this->request->get('id');
		$relatedProductID = (int)$this->request->get('relatedProductID');

		$relatedProduct = Product::getInstanceByID($relatedProductID);
		$product = Product::getInstanceByID($productID);

		$product->removeFromRelatedProducts($relatedProduct);
		$product->save();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function sort()
	{
		$product = Product::getInstanceByID((int)$this->request->get('id'));
		$target = $this->request->get('target');
		preg_match('/_(\d+)$/', $target, $match); // Get group.

		foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
		{
			if(empty($key)) continue;

			$relationship = ProductRelationship::getInstance($product, Product::getInstanceByID((int)$key));
			$relationship->position->set((int)$position);

			if(isset($match[1])) $relationship->productRelationshipGroup->set(ProductRelationshipGroup::getInstanceByID((int)$match[1]));
			else $relationship->productRelationshipGroup->setNull();

			$relationship->save();
		}

		return new JSONResponse(false, 'success');
	}
}

?>