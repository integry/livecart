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
		$productID = (int)$this->request->get('id');
		$product = Product::getInstanceByID($productID, ActiveRecord::LOAD_DATA, array('Category'));


		$this->set('categoryID', $product->category->getID());
		$this->set('productID', $productID);
		$this->set('relationships', $product->getRelationships($this->request->get('type'))->toArray());
		$this->set('relationshipsWithGroups', $product->getRelatedProductsWithGroupsArray($this->request->get('type')));
		$this->set('type', $this->request->get('type'));

	}

	/**
	 * Products popup
	 *
	 * @role update
	 */
	public function selectProductAction()
	{


		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());
		$this->set("categoryList", $categoryList->toArray());

	}

	/**
	 * Creates new relationship
	 *
	 * @role update
	 */
	public function addRelatedAction()
	{
		$productID = (int)$this->request->get('id');
		$relatedProductID = (int)$this->request->get('relatedownerID');

		$relatedProduct = Product::getInstanceByID($relatedProductID, true, array('DefaultImage' => 'ProductImage'), Product::LOAD_DATA);
		$product = Product::getInstanceByID($productID, Product::LOAD_DATA);

		if(!$relatedProduct->isRelatedTo($product, $this->request->get('type')))
		{
			try
			{
				$product->addRelatedProduct($relatedProduct, $this->request->get('type'));
				$product->save();


				$this->set('product', $relatedProduct->toArray());
				$this->set('added', true);
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
		$productID = (int)$this->request->get('id');
		$relatedProductID = (int)$this->request->get('relatedownerID');

		$relatedProduct = Product::getInstanceByID($relatedProductID);
		$product = Product::getInstanceByID($productID);

		$product->removeFromRelatedProducts($relatedProduct, $this->request->get('type'));
		$product->save();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function sortAction()
	{
		$target = $this->request->get('target');

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

		$product = Product::getInstanceByID($this->request->get('id'));

		$type = $this->request->get('type');

		preg_match('/_(\d+)$/', $target, $match); // Get group.
		if (substr($target, 0, 8) == 'noGroup_')
		{
			$match = array();
		}

		foreach($this->request->get($target, null, array()) as $position => $key)
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
				$relationship->productRelationshipGroup = null;
			}

			$relationship->save();
		}

		return new JSONResponse(false, 'success');
	}
}

?>