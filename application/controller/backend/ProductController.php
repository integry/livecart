<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("library.DataGrid.*");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @role admin.store.product
 */
class ProductController extends StoreManagementController {


	public function index()
	{
		ClassLoader::import("application.model.category.Category");

		$category = Category::getInstanceByID($this->request->getValue("id"));
		$path = $category->getPathNodes();

		$response = new ActionResponse();
		$response->setValue("path", $path);
		return $response;
	}

	/**
	 * Shows products search form.
	 * @return ActionResponse
	 */
	/*
    public function index()
    {
		$count = Product::getProductsCount();

	  	$filter = new DataGridFilter("Product", $this->request->toArray());
		$filter->selector()->addField("Product.sku", "sku");
		$filter->selector()->addField("Product.URL", "URL");

		$filter->sorter()->addField("Product.sku", "sku");
		$filter->sorter()->addField("Product.URL", "URL");
		$filter->pager()->setOptions($count, 10);

		$display = new DataGridFilterDisplayer($filter);

		$recordSet = Product::getRecordSet("Product", $filter->getArSelectFilter(), true);

		//datagrid
		$grid =	new DataGridArrayDisplayer();
		$grid->setDataSource(Product::getArrayFromArSet($recordSet, "en"));
		$grid->setSortedFields($filter->sorter()->getFields());

		$grid->addColumn("ID", "ID", "", "width: 30px");
		$grid->addColumnComplex(array("ID", "name"), get_class($this), 'formatProduct', "Product", "width: 250px");
		$grid->addColumn("sku", "sku", "", "width: 50px");


		//response
		$response = new ActionResponse();
		$response->setValue('filter', $display->display());
		$response->setValue('grid', $grid->display());

		//application rendering
		$app = Application::getInstance();
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/document.js");
		$app->getRenderer()->appendValue("BODY_ONLOAD", $display->displayOnLoad(1, 1));
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/DataGrid/datagrid.js");

		return $response;
	}
	*/

	/**
	 * @return string
	 */
	public static function formatProduct($params) {

		return "<a href=\"".Router::getInstance()->createUrl(array("controller" => "backend.product", "action" => "viewForm", "id" => $params["ID"]))."\">".$params['name']."</a>";
		//return substr($params["Product.dateCreated"], 0, 10);
	}

  	/**
  	 * Shows product add form
  	 * @return ActionResponse
  	 */
  	public function addForm() {

		//tree
		$treeDHTML = $this->createTreeDHTML();

		//form
		$form = $this->createProductForm(array());
		$form->setAction(Router::getInstance()->createUrl(array('controller' => $this->request->getControllerName(), 'action' => 'save')));
		if ($form->validationFailed()) {

			$form->restore();
		}

	    //response
	  	$response = new ActionResponse();
		$this->renderAjaxJsFiles($response);//ajax JS files
	  	$response->setValue("tree", $treeDHTML->toHTML());

		if ($this->request->isValueSet("id")) {

			$response->setValue("jsMarkNode", $treeDHTML->jsSelectNode($this->request->getValue("id"), 'treeMenuNode', 'treeMenuNodeSelected'));
		  	//hidden field of selected
			$response->appendValue("BODY_ONLOAD", array("changeCatalog(".$this->request->getValue("id").")"));
	  	}

		$response->setValue("form", @$form->render());
	  	return $response;
	}


	/**
	 * Shows product view form
	 * @return ActionResponse
	 */
	public function viewForm() {

	  	//product data
	  	$product = Product::getInstanceById("Product", $this->request->getValue("id"), true, true);
		$productArray = $product->toArray();
		$productArray = array_merge($productArray, $productArray['lang']['en']);

		//tree
		$treeDHTML = $this->createTreeDHTML();

	  	//form
	  	$form = $this->createProductForm($productArray);

		$form->setAction(Router::getInstance()->createUrl(array("controller" => $this->request->getControllerName(), "action" => "save", "id" => $this->request->getValue("id"))));
		if ($form->validationFailed()) {

			$form->restore();
		}

	  	//response
	  	$response = new ActionResponse();
		$this->renderAjaxJsFiles($response);//ajax JS files
	  	$response->setValue("tabPanelFile", "tabpanel.viewForm.tpl");
	  	$response->setValue("id", $this->request->getValue("id"));
	  	$response->setValue("action", "viewForm");
		$response->setValue("form", @$form->render());
		$response->setValue("tree", $treeDHTML->toHTML());
		$response->setValue("jsMarkNode", $treeDHTML->jsSelectNode($product->catalog->get()->getId(), 'treeMenuNode', 'treeMenuNodeSelected'));
	  	//hidden field of selected
	  	$app = Application::getInstance();
		$app->getRenderer()->appendValue("BODY_ONLOAD", "changeCatalog(".$product->catalog->get()->getId().");");

		return $response;
	}

	/**
	 * Shows product prices and shiping form.
	 * return @ActionResponse
	 */
	public function priceForm() {

		//currencies
		$currSet = ActiveRecord::getRecordSet("Currency", new ArSelectFilter(), true);
		$default = Currency::getDefaultCurrency($currSet);

	  	//form
		$form = $this->createPriceForm(array(), $currSet, $default);
	  	$form->setAction(Router::getInstance()->createUrl(array("controller" => $this->request->getControllerName(), "action" => "savePrice", "id" => $this->request->getValue("id"))));
	  	$form->setAttribute("onsubmit", "discount.onSubmit();");

	  	//discountHandler
	  	$discountHandler = new DiscountHandler();

		if ($form->validationFailed() || $discountHandler->validationFailed()) {

			$form->restore();
			$discountHandler->restore();
		} else {

		  	$product = ActiveRecord::getInstanceById("Product", $this->request->getValue("id"), true);
			$form->setData(array_merge($product->toArray(), $product->getPriceArray()));
			$discountHandler->loadDataFromDb($product);
		}

	  	//response
	  	$response = new ActionResponse();
	  	$response->appendValue("JAVASCRIPT", array(Router::getInstance()->getBaseDir()."/public/javascript/discounts/discount.js"));
	  	$response->setValue("tabPanelFile", "tabpanel.priceForm.tpl");
		$response->setValue("action", "priceForm");
		$response->setValue("id", $this->request->getValue("id"));
		$response->setValue("currency", $this->locale->getCurrency($default->getId()));
		$response->setValue("form", $form);

		//body onload
		$app = Application::getInstance();
		$app->getRenderer()->appendValue("BODY_ONLOAD", "changeMetrics(document.priceForm.unitsType.value);".$discountHandler->createJs($this->locale->getCurrency($default->getId())));

		return $response;
	}

	/**
	 * Products images action.
	 * @return ActionResponse
	 */
	public function imagesForm() {

		$product =	Product::getInstanceById("Product", $this->request->getValue("id"), false);
	  	//loading images

		$images = array();
		//try {
			$images = $product->getImagesSet()->toArray();
		//} catch (Exception $ex ){

		//}

		$j = 0;
		$layers = array();
		foreach ($images as $value) {

		  	$layers[$j]['imgID'] = $value['ID'];
		  	$layers[$j]['imgSource'] = Router::getInstance()->getBaseDir()."/public/".$this->uploadDir."/thumbs/".$value['ID'].".jpg";
		  	@$layers[$j]['imgDesc'] = $value['lang']['en']['title'];
		  	$j ++;
		}

	  	//form

		$form = $this->createImagesForm();

	  	//response

	  	$response = new ActionResponse();

	  	$response->setValue("tabPanelFile", "tabpanel.imagesForm.tpl");
	  	$response->setValue("action", "imagesForm");
		$response->setValue("id", $this->request->getValue("id"));
		$response->setValue("form", @$form->render());
		$response->setValue("showDiv", true);
		$response->setValue("layers", $layers);

		$response->setValue("imageScript",
			"<script language=\"javascript\">\n".
			"productImages.updateLink = '".Router::getInstance()->createUrl(array("controller" => $this->request->getControllerName(), "action" => "saveImage"))."'; \n".
			"productImages.deleteLink = '".Router::getInstance()->createUrl(array("controller" => $this->request->getControllerName(), "action" => "deleteImage"))."'; \n".
			"productImages.descriptionLink = '".Router::getInstance()->createUrl(array("controller" => $this->request->getControllerName(), "action" => "updateDescription"))."'; \n".
			"productImages.sortLink = '".Router::getInstance()->createUrl(array("controller" => $this->request->getControllerName(), "action" => "updateSorting", "id" => $this->request->getValue("id")))."'; \n".

			"jsLocales['_save'] = '".$this->locale->translate("_save")."'; \n".
			"jsLocales['_cancel'] = '".$this->locale->translate("_cancel")."'; \n".
			"jsLocales['_imageFileNotDefined'] = '".$this->locale->translate("_imageFileNotDefined")."'; \n".
			"jsLocales['_notCorrectImageFile'] = '".$this->locale->translate("_notCorrectImageFile")."'; \n".
			"jsLocales['_removeImage?'] = '".$this->locale->translate("_removeImage?")."'; \n".


			"</script>");

		//
		$app = Application::getInstance();
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/ajax.js");
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/productImages/images.js");
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/scriptaculous/lib/prototype.js");
  		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/scriptaculous/src/scriptaculous.js");
		return $response;
	}

	/**
	 * Saves product.
	 * @return ActionRedirectResponse
	 */
	public function save() {

	  	$form = $this->createProductForm($this->request->toArray());

		if ($form->isValid()) {

			if ($this->request->isValueSet("id")) {

			  	$product = Product::getInstanceById("Product", $this->request->getValue("id"));
			} else {

			 	$product = Product::getNewInstance("Product");
			}

			$product->catalog->set(Catalog::getInstanceById($this->request->getValue("catalogId")));
			$product->dateCreated->set(date("Y-m-d"));
			$product->sku->set($form->getField("sku")->getValue());
			$product->status->set($form->getField("status")->getValue());
			$product->lang("en")->name->set($form->getField("name")->getValue());
			$product->lang("en")->shortDescription->set($form->getField("shortDescription")->getValue());
			$product->lang("en")->fullDescription->set($form->getField("fullDescription")->getValue());
			$product->URL->set($form->getField("URL")->getValue());
			$product->isBestSeller->set($form->getField("isBestSeller")->getValue());
			$product->type->set($form->getField("type")->getValue());

			$product->save();

			//inserting empty prices
			if (!$this->request->isValueSet("id")) {

				$currSet = Currency::getCurrencies();
				foreach ($currSet as $curr) {

					$price = ActiveRecord::getNewInstance("ProductPrice");
					$price->product->set($product);
					$price->currency->set($curr);
					$price->save();
				}
			}

			return new ActionRedirectResponse($this->request->getControllerName(), "viewForm", array("id" => $product->getId()));

		} else {

			$form->saveState();

			if ($this->request->isValueSet("id")) {

				return new ActionRedirectResponse($this->request->getControllerName(), "viewForm", array("id" => $this->request->getValue("id")));
			} else {

				return new ActionRedirectResponse($this->request->getControllerName(), "addForm", array("id" => $this->request->getValue("catalogId")));
			}
		}
	}

	/**
	 * Saves prices and shipping of product.
	 * @return ActionRedirectesponse
	 */
	public function savePrice() {

		//currencies
		$currSet = ActiveRecord::getRecordSet("Currency", new ArSelectFilter(), true);
		$default = Currency::getDefaultCurrency($currSet);

		$requestArray = $this->request->toArray();

		//discount handler
		$discountHandler = new DiscountHandler();
		$discountHandler->proccessRequestData($requestArray);

		//form
		$form =	$this->createPriceForm($requestArray, $currSet, $default);

		$valid = $form->isValid();
		$valid = $discountHandler->isValid() && $valid;
		if ($valid) {

			$product = Product::getInstanceById("Product", $this->request->getValue("id"));

			$product->minimumQuantity->set($form->getField("minimumQuantity")->getValue());
			$product->shippingSurgageAmount->set($form->getField("shippingSurgageAmount")->getValue());

			$product->isSeparateShipment->set($form->getField("isSeparateShipment")->getValue());
			$product->isFreeShipping->set($form->getField("isFreeShipping")->getValue());

		  	$product->shippingWidth->set($form->getField("shippingWidth")->getValue());//
			$product->shippingHeight->set($form->getField("shippingHeight")->getValue());
			$product->shippingLength->set($form->getField("shippingLength")->getValue());
			$product->shippingWeight->set($form->getField("shippingWeight")->getValue());
			$product->unitsType->set($form->getField("unitsType")->getValue());
			$product->save();

			$currSet = Currency::getCurrencies();
			foreach ($currSet as $curr) {

				$price = ActiveRecord::getInstanceById("ProductPrice", array("productID" => $product->getId(), "currencyID" => $curr->getId()));

				if ($form->getField($curr->getId())->getValue() != "") {

					$price->price->set($form->getField($curr->getId())->getValue());
				} else {

				  	$price->price->setNull();
				}
				$price->save();
			}

			$filter = new ArDeleteFilter();
		  	$filter->setCondition(new EqualsCond(new ArFieldHandle("Discount", "productID"), $product->getId()));
		  	$discountSet = ActiveRecord::deleteRecordSet("Discount", $filter);

			foreach ($discountHandler->getDiscounts() as $value) {

				$discount = ActiveRecord::getNewInstance("Discount");
				$discount->product->set($product);
				$discount->amount->set($value['amount']);
				$discount->discountType->set($value['discountType']);
				$discount->discountValue->set($value['discountValue']);
				$discount->save();
			}
		} else {

			$form->saveState();
			$discountHandler->saveState();
		}

		return new ActionRedirectResponse($this->request->getControllerName(), "priceForm", array("id" => $this->request->getValue("id")));
	}

	/**
	 * Updates sorting of images.
	 * @return JsonResponse
	 */
	public function updateSorting() {

		$sorting = split('-', $this->request->getValue('serialized'));
		$i = 1;
		foreach ($sorting as $value) {

			$imageID = substr($value, strpos($value, '=') + 1);

			$image = ActiveRecord::getInstanceById("ProductImage", $imageID, false);
			$image->position->set($i ++);
			$image->save();
		}

	  	//response
	  	$response = new JsonResponse();
		$response->setValue('ok', true);
	  	return $response;
	}


	/**
	 * Updates title of image.
	 * @return JsonResponse
	 */
	public function updateDescription() {

		$image = ProductImage::getInstanceById("ProductImage", $this->request->getValue("imageID"), true);
		$image->lang('en')->title->set($this->request->getValue("title"));
		$image->save();

		//response
	  	$response = new JsonResponse();
	  	$response->setValue('id', $this->request->getValue("imageID"));
		$response->setValue('title', $image->lang('en')->title->get());
	  	return $response;
	}

	/**
	 * Deletes image.
	 * @return JsonResponse
	 */
	public function deleteImage() {

	  	ActiveRecord::deleteById("ProductImage", $this->request->getValue("imageId"));

		@unlink($this->uploadDir."thumbs/".$this->request->getValue("imageId").".jpg");
		@unlink($this->uploadDir."medium/".$this->request->getValue("imageId").".jpg");
		@unlink($this->uploadDir."large/".$this->request->getValue("imageId").".jpg");

		//response
	  	$response = new JsonResponse();
		$response->setValue('deletedID', $this->request->getValue("imageId"));
		return $response;
	}

	/**
	 * Saves image. Action in iframeUpload iframe.
	 * @return RawResponse
	 */
	public function saveImage() {

	  	ClassLoader::import("library.uploader.*");
	  	if (empty($_FILES)) {

		    exit();
		}

	  	if (!empty($_FILES) && is_array($_FILES['imageFile'])
		  		&& empty($_FILES['imageFile']['error'])) {

		    //checking img with imageuploader
		    $uploader = new ImageUploader($_FILES['imageFile']);
			$ext = $uploader->getExtension();

		    if (!$ext) {

			  	$response = new RawResponse();
				$response->setContent("<script>window.parent.killWait(); alert('Bad image file!');</script>");
				return $response;
			}

		    //saving image
			if ($this->request->isValueSet("imageID")) {

				@unlink($this->uploadDir."thumbs/".$oldID.".jpg");
				@unlink($this->uploadDir."medium/".$oldID.".jpg");
				@unlink($this->uploadDir."large/".$oldID.".jpg");

				$imageID = $this->request->getValue("imageID");
			} else {

				$product = ActiveRecord::getInstanceById("Product", $this->request->getValue("id"), true);

				$image = ProductImage::getNewInstance("ProductImage");
				$image->product->set($product);
				$image->lang("en")->title->set($this->request->getValue("title"));
				$image->position->set($product->getMaxImagePosition() + 1);
				$image->save();

				$imageID = $image->getId();
			}

			//resizing and copying images
			//$resizer = $this->serviceLocator->get('imageresizer');
			ImageResizer::gdCreateThumbnail(Configuration::getInstance()->getValue('thumbsImageSize'),
											 Configuration::getInstance()->getValue('thumbsImageSize'),
											$_FILES['imageFile']['tmp_name'],
											$this->uploadDir."thumbs/".$imageID.".jpg", $ext, 75);

			ImageResizer::gdCreateThumbnail(Configuration::getInstance()->getValue('mediumImageSize'),
											 Configuration::getInstance()->getValue('mediumImageSize'),
											$_FILES['imageFile']['tmp_name'],
											$this->uploadDir."medium/".$imageID.".jpg", $ext, 75);

			ImageResizer::gdCreateThumbnail(Configuration::getInstance()->getValue('largeImageSize'),
											 Configuration::getInstance()->getValue('largeImageSize'),
											$_FILES['imageFile']['tmp_name'],
											$this->uploadDir."large/".$imageID.".jpg", $ext, 75);

			//response
			if (!$this->request->isValueSet("imageID")) {

			  	$layers[0]['imgID'] = $image->getId();
			  	$layers[0]['imgSource'] = Router::getInstance()->getBaseDir()."/public/".$this->uploadDir."/thumbs/".$image->getId().".jpg";
		  		$layers[0]['imgDesc'] = $image->lang("en")->title->get();

				$renderer = new	TemplateRenderer(Router::getInstance());
				$renderer->setValue("id", $this->request->getValue("id"));
				$renderer->setValue("layers", $layers);
				$output = $renderer->render("backend/product/image.tpl");

				$output = str_replace("\n", "", $output);
				$output = str_replace("\r", "", $output);
				$output = str_replace("\"", "\\\"", $output);
				$output = str_replace("\'", "\\\'", $output);

				$response = new RawResponse();
				$response->setContent("<script>
					window.parent.killWait();
					window.parent.productImages.addImage(".$image->getId().", \"".$output."\");
				  </script>");
			} else 	{

				$response = new RawResponse();
				$response->setContent("<script>
					window.parent.killWait();
					window.parent.productImages.changeImage(".$imageID.", \"".Router::getInstance()->getBaseDir()."/public/".$this->uploadDir."/thumbs/".$imageID.".jpg?".rand(1, 1000000)."\");
				  </script>");
			}

			return $response;
		}

		$response = new RawResponse();
		$response->setContent("<script>window.parent.killWait(); alert('Error uploading image!');</script>");
		return $response;
	}

	/**
	 * Creates product add/view form
	 * @param array $data Initial values
	 * @return Form
	 */
	private function createProductForm($data) {

	  	ClassLoader::import("library.formhandler.*");
	  	ClassLoader::import("library.formhandler.check.string.*");

		$form = new Form("productForm", $data);

		$sectMain = new Section("Main details");
		$form->addSection($sectMain);

		$sectCharacteristics = new Section($this->locale->translate("_productCharacteristics"));
		$form->addSection($sectCharacteristics);

		$field = new HiddenField("catalogId", '');
		$field->addCheck(new RequiredValueCheck($this->locale->translate("_catalogRequired.")));
		$form->addField($field);

		$field = new TextLineField("sku", $this->locale->translate("_sku"));
		$form->addField($field);

		$field = new SelectField("status", $this->locale->translate("_availability"));
		$field->addValue(1, $this->locale->translate("_available"));
		$field->addValue(0, $this->locale->translate("_not_avaible"));
		$field->addValue(2, $this->locale->translate("_disabled"));
		$field->setValue(1);
		$sectMain->addField($field);

		$field = new TextLineField("name", $this->locale->translate("_productName"));
		$field->addCheck(new MinLengthCheck($this->locale->translate("_nameMustBeAtLeast2CharsLength"), 2));
		$sectMain->addField($field);

		$field = new TextAreaField("shortDescription", $this->locale->translate("_shortDescription"));
		$sectMain->addField($field);

		$field = new TextAreaField("fullDescription", $this->locale->translate("_fullDescription"));
		$sectMain->addField($field);

		$field = new TextLineField("URL", $this->locale->translate("_URL"));
		$field->setAttribute("style", 'width: 320px');
		$sectMain->addField($field);

		$field = new CheckboxField("isBestSeller", $this->locale->translate("_isBestSelller"));
		$field->setInitialValue(1);
		$sectMain->addField($field);

		$field = new SelectField("type", $this->locale->translate("_type"));
		$field->addValue(1, $this->locale->translate("_tangible"));
		$field->addValue(0, $this->locale->translate("_intangible"));
		$field->setValue(1);
		$sectCharacteristics->addField($field);

		$sectCharacteristics->addField(new SubmitField("submit", $this->locale->translate("_save")));

		$form->getField("name")->setAttribute("maxlength", 100);


		return $form;
	}

	/**
	 * Creates product price and shipping form.
	 * $data array Initial form values
	 * $currSet ArSet
     * $defaultCurrency ActiveRecord
	 * @return Form
	 */
	private function createPriceForm($data, $currSet, $defaultCurrency) {

	  	ClassLoader::import("library.formhandler.*");
	  	ClassLoader::import("library.formhandler.check.string.*");
	  	ClassLoader::import("library.formhandler.filter.*");

		$form = new Form("priceForm", $data);

		$sectPrices = new Section($this->locale->translate("_pricing"));
		$sectOrdering = new Section($this->locale->translate("_orderingAndShipping"));
		$sectDimensions = new Section($this->locale->translate("_productDimensions"));

		$form->addSection($sectPrices);
		$form->addSection($sectOrdering);
		$form->addSection($sectDimensions);


		$field = new TextLineField($defaultCurrency->getId(), $this->locale->translate("_price")." ( ".$this->locale->getCurrency($defaultCurrency->getId())." ) ");
		$field->addCheck(new IsNumericCheck($this->locale->translate("Price must be numeric!")));
		$field->addFilter(new NumericFilter());
		$field->setAttribute("style", 'width: 80px');
		$field->setAttribute("maxlength", '10');
		$sectPrices->addField($field);

		//Price fiels- other currencies
		foreach ($currSet as $curr) {

		  	if (!$curr->isDefault->get()) {

			 	$field = new TextLineField($curr->getId(), $this->locale->translate("_price")." ( ".$this->locale->getCurrency($curr->getId())." ) ");
				$field->addFilter(new NumericFilter());
				$field->addCheck(new IsNumericCheck($this->locale->translate("_priceNumeric"), true));
				$field->setAttribute("style", 'width: 80px');
				$field->setAttribute("maxlength", '10');
				$sectPrices->addField($field);
			}
		}

		//Discounts
		$field = new TextLineField("minimumQuantity", $this->locale->translate("_minimumQuantity"));
		$field->addCheck(new IsIntegerCheck($this->locale->translate("_minimumQuantityInteger")));
		$field->addFilter(new TrimFilter());
		$field->setAttribute("style", 'width: 50px');
		$field->setAttribute("maxlength", '5');
		$sectOrdering->addField($field);

		$field = new TextLineField("shippingSurgageAmount", $this->locale->translate("_shippingSurgageAmount"));
		$field->addCheck(new IsNumericCheck($this->locale->translate("_shippingSurgageNumeric")));
		$field->addFilter(new NumericFilter());
		$field->setAttribute("style", 'width: 50px');
		$field->setAttribute("maxlength", '10');
		$sectOrdering->addField($field);

		$field = new CheckboxField("isSeparateShipment", $this->locale->translate("_isSeparateShipment"), 1);
		$sectOrdering->addField($field);

		$field = new CheckboxField("isFreeShipping", $this->locale->translate("_isFreeShipping"), 1);
		$sectOrdering->addField($field);

		//Dimensions
		$field = new TextLineField("shippingHeight", $this->locale->translate("_height"));
		$field->addCheck(new IsNumericCheck($this->locale->translate("_heightNumeric"), true));
		$field->addFilter(new NumericFilter());
		$field->setAttribute("style", 'width: 80px');
		$field->setAttribute("maxlength", '10');
		$sectDimensions->addField($field);

		$field = new TextLineField("shippingWidth", $this->locale->translate("_width"));
		$field->addCheck(new IsNumericCheck($this->locale->translate("_widthNumeric"), true));
		$field->addFilter(new NumericFilter());
		$field->setAttribute("style", 'width: 80px');
		$field->setAttribute("maxlength", '10');
		$sectDimensions->addField($field);

		$field = new TextLineField("shippingLength", $this->locale->translate("_length"));
		$field->addCheck(new IsNumericCheck($this->locale->translate("_lenghtNumeric"), true));
		$field->addFilter(new NumericFilter());
		$field->setAttribute("style", 'width: 80px');
		$field->setAttribute("maxlength", '10');
		$sectDimensions->addField($field);

		$field = new TextLineField("shippingWeight", $this->locale->translate("_weight"));
		$field->addCheck(new IsNumericCheck($this->locale->translate("_weightNumeric"), true));
		$field->addFilter(new NumericFilter());
		$field->setAttribute("style", 'width: 80px');
		$field->setAttribute("maxlength", '10');
		$sectDimensions->addField($field);

		$field = new HiddenField("unitsType");
		$sectDimensions->addField($field);

		$sectButton = new Section();
		$form->addSection($sectButton);
		$sectButton->addField(new SubmitField("submit", $this->locale->translate("_save")));

		return $form;
	}

	/**
	 * Creates product images form.
	 * @return Form
	 */
	private function createImagesForm() {

		ClassLoader::import("library.formhandler.*");

		$form = new Form("imagesForm", array());

		$form->setAction(Router::getInstance()->createUrl(array("controller" => $this->request->getControllerName(), "action" => "saveImage", "id" => $this->request->getValue("id"))));

		$form->setAttribute("target", "iframeUpload");
		$form->setAttribute("onsubmit", "if (validateForm(this)) {popWait(); return true;} else {return false;}");

		$field = new TextLineField("title", $this->locale->translate("_title"));
	  	$form->addField($field);

		$field = new UploadField("imageFile", $this->locale->translate("_uploadAnImage"));
		$field->addCheck(new RequiredValueCheck($this->locale->translate("_imageFileNotDefined")));
		$field->addCheck(new UploadImageCheck($this->locale->translate("_notCorrectImageFile")));
	  	$form->addField($field);

		$form->addField(new SubmitField("submit", $this->locale->translate("_save")));

		return $form;
	}

	/**
	 *
	 * @return Ajax_TreeMenu_DHTML
	 */
	private function createTreeDHTML() {

		$groups = TreeCatalog::getAllTree("TreeCatalog", true);

		$treemenu =	new AJAX_TreeMenu();
		$this->formatTreeMenu($treemenu, $groups);
		$treemenuDHTML = &new AJAX_TreeMenu_DHTML("", "objTreeMenuAjaxAdd", $treemenu, array('images' => Router::getInstance()->getBaseDir().'/library/AJAX_TreeMenu/imagesAlt2', 'defaultClass' => 'treeMenuDefault'));

		return $treemenuDHTML;
	}

	/**
	 */
	private function formatTreeMenu($treemenu, $tree) {

	  	foreach ($tree as $child) {

			$array['text'] = $child->lang("en")->name->get();
			$array['link'] = "javascript: changeCatalog(".$child->getId().");";
			$array['cssClass'] = 'treeMenuNode';
			$array['cssSelected'] = 'treeMenuNodeSelected';

			$node =	&new AJAX_TreeNode($child->getId(), $array);
			$treemenu->addItem($node);
			$this->formatTreeMenu($node, $child);
		}
	}

}




















?>