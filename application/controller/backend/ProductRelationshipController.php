<?php


/**
 * Manage related products
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class ProductRelationshipController extends StoreManagementController
{
	public function indexAction()
	{
		$productID = (int)$this->request->gget('id');
		$product = Product::getInstanceByID($productID, ActiveRecord::LOAD_DATA, array('Category'));

		$response = new ActionResponse();
		$response->set('categoryID', $product->category->get()->getID());
		$response->set('productID', $productID);
		$response->set('relationships', $product->getRelationships($this->request->gget('type'))->toArray());
		$response->set('relationshipsWithGroups', $product->getRelatedProductsWithGroupsArray($this->request->gget('type')));
		$response->set('type', $this->request->gget('type'));

		return $response;
	}

	/**
	 * Products popup
	 *
	 * @role update
	 */
	public function selectProductAction()
	{
		$response = new ActionResponse();

		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());
		$response->set("categoryList", $categoryList->toArray());

		return $response;
	}

	/**
	 * Creates new relationship
	 *
	 * @role update
	 */
	public function addRelatedAction()
	{
		$productID = (int)$this->request->gget('id');
		$relatedProductID = (int)$this->request->gget('relatedownerID');

		$relatedProduct = Product::getInstanceByID($relatedProductID, true, array('DefaultImage' => 'ProductImage'), Product::LOAD_DATA);
		$product = Product::getInstanceByID($productID, Product::LOAD_DATA);

		if(!$relatedProduct->isRelatedTo($product, $this->request->gget('type')))
		{
			try
			{
				$product->addRelatedProduct($relatedProduct, $this->request->gget('type'));
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
	public function deleteAction()
	{
		$productID = (int)$this->request->gget('id');
		$relatedProductID = (int)$this->request->gget('relatedownerID');

		$relatedProduct = Product::getInstanceByID($relatedProductID);
		$product = Product::getInstanceByID($productID);

		$product->removeFromRelatedProducts($relatedProduct, $this->request->gget('type'));
		$product->save();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function sortAction()
	{
		$target = $this->request->gget('target');

		if (!$target)
		{
			foreach ($this->request->toArray() as $key => $value)
			{
				if (is_array($value))
				{
					$target = $key;
					break;
				}
			}
		}

		$product = Product::getInstanceByID($this->request->gget('id'));

		$type = $this->request->gget('type');

		preg_match('/_(\d+)$/', $target, $match); // Get group.
		if (substr($target, 0, 8) == 'noGroup_')
		{
			$match = array();
		}

		foreach($this->request->gget($target, array()) as $position => $key)
		{
			if(empty($key)) continue;

			$relationship = ProductRelationship::getInstance($product, Product::getInstanceByID((int)$key), $type);
			$relationship->position->set((int)$position);

			if(isset($match[1]))
			{
				$relationship->productRelationshipGroup->set(ProductRelationshipGroup::getInstanceByID((int)$match[1]));
			}
			else
			{
				$relationship->productRelationshipGroup->setNull();
			}

			$relationship->save();
		}

		return new JSONResponse(false, 'success');
	}
}

?>