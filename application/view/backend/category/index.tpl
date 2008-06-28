{includeJs file="library/livecart.js"}
{includeJs file="library/KeyboardEvent.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/ActiveList.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/lightbox/lightbox.js"}

{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}

{includeJs file="backend/Product.js"}
{includeJs file="backend/Category.js"}
{includeJs file="backend/SpecField.js"}
{includeJs file="backend/Filter.js"}
{includeJs file="backend/ObjectImage.js"}
{includeJs file="backend/Product.js"}
{includeJs file="backend/RelatedProduct.js"}
{includeJs file="backend/ProductFile.js"}
{includeJs file="backend/ProductOption.js"}

{include file="backend/eav/includes.tpl"}

{includeCss file="library/ActiveList.css"}
{includeCss file="library/ActiveGrid.css"}
{includeCss file="backend/Category.css"}
{includeCss file="backend/Product.css"}
{includeCss file="backend/SpecField.css"}
{includeCss file="backend/ProductRelationship.css"}
{includeCss file="backend/ProductFile.css"}
{includeCss file="backend/ProductOption.css"}
{includeCss file="backend/Filter.css"}
{includeCss file="backend/CategoryImage.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/lightbox/lightbox.css"}

{pageTitle help="cat"}<span id="activeCategoryPath"></span><span id="activeProductPath" style="display: none;"><span id="productCategoryPath"></span><span id="activeProductName"></span></span><span style="display: none;">{t _products_and_categories}</span>{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="specField_item_blank" class="dom_template">{include file="backend/specField/form.tpl"}</div>
<div id="specField_group_blank" class="dom_template">{include file="backend/specField/group.tpl"}</div>
<div id="filter_item_blank" class="dom_template">{include file="backend/filterGroup/form.tpl"}</div>
<div id="productRelationshipGroup_item_blank" class="dom_template">{include file="backend/productRelationshipGroup/form.tpl"}</div>
<div id="productFileGroup_item_blank">{include file="backend/productFileGroup/form.tpl"}</div>
<div id="productFile_item_blank">{include file="backend/productFile/form.tpl"}</div>
<div id="productOption_item_blank" class="dom_template">{include file="backend/productOption/form.tpl"}</div>

<div id="confirmations">
	<div id="redZone">
		<div id="productRelationshipCreateFailure" class="redMessage" style="display: none;">
			{img class="closeMessage" src="image/silk/cancel.png" }
			<div>{t _could_not_create_product_relationship}</div>
		</div>
		<div id="productFileSaveFailure" class="redMessage" style="display: none;">
			{img class="closeMessage" src="image/silk/cancel.png"}
			<div>{t _could_not_save_product_file}</div>
		</div>
		<div id="productImageSaveFailure" class="redMessage" style="display: none;">
			{img class="closeMessage" src="image/silk/cancel.png"}
			<div>{t _could_not_save_product_image}</div>
		</div>
	</div>
	<div id="yellowZone">
		<div id="categoryImageSaved" class="yellowMessage" style="display: none;">
			{img class="closeMessage" src="image/silk/cancel.png"}
			<div>{t _category_image_was_successfully_saved}</div>
		</div>
		<div id="productImageSaved" class="yellowMessage" style="display: none;">
			{img class="closeMessage" src="image/silk/cancel.png"}
			<div>{t _product_image_was_successfully_saved}</div>
		</div>
		<div id="productFileSaved" class="yellowMessage" style="display: none;">
			{img class="closeMessage" src="image/silk/cancel.png"}
			<div>{t _product_file_was_successfully_saved}</div>
		</div>
		<div id="productRelationshipCreated" class="yellowMessage" style="display: none;">
			{img class="closeMessage" src="image/silk/cancel.png"}
			<div>{t _a_relationship_between_products_was_successfully_created}</div>
		</div>
	</div>
</div>

<div id="catgegoryContainer" class="treeContainer  maxHeight h--60">
	<div id="categoryBrowser" class="treeBrowser"></div>

	<br />

	{allowed role="category.create,category.remove,category.sort"}
		{t _with_selected_category}:

		<ul id="categoryBrowserActions" class="verticalMenu">

			{allowed role="category.create"}
				<li class="addTreeNode">
					<a href="#" id="createNewCategoryLink">
						{t _create_subcategory}
					</a>
				</li>
			{/allowed}

			{allowed role="category.sort"}
				<li class="moveUpTreeNode">
					<a href="#" id="moveCategoryUp">
						{t _move_category_up}
					</a>
				</li>
				<li class="moveDownTreeNode">
					<a href="#" id="moveCategoryDown">
						{t _move_category_down}
					</a>
				</li>
			{/allowed}

			{allowed role="category.remove"}
				<li class="removeTreeNode">
					<a href="#" id="removeCategoryLink">
						{t _remove_category}
					</a>
				</li>
			{/allowed}
		</ul>

	{/allowed}

</div>

<div id="managerContainer" class="treeManagerContainer maxHeight h--60">

	<div id="loadingProduct" style="display: none; position: absolute; text-align: center; width: 100%; padding-top: 200px; z-index: 50000;">
		<span style="padding: 40px; background-color: white; border: 1px solid black;">{t _loading_product}<span class="progressIndicator"></span></span>
	</div>

	<div id="categoryTabs">
		<div id="tabContainer" class="tabContainer">
			<ul id="tabList" class="tabList tabs">

				{allowed role="product"}
				<li id="tabProducts" class="tab active">
					<a href="{link controller=backend.product action=index id=_id_}">{t _products}</a>
					<span> </span>
					<span class="tabHelp">products</span>
				</li>
				{/allowed}

				<li id="tabMainDetails" class="tab inactive" {denied role="category"}style="display: none"{/denied}>
					<a href="{link controller=backend.category action=form id=_id_}">{t _category_details}</a>
					<span> </span>
					<span class="tabHelp">categories.details</span>
				</li>

				<li id="tabFields" class="tab inactive" {denied role="category"}style="display: none"{/denied}>
					<a href="{link controller=backend.specField action=index id=_id_}">{t _attributes}</a>
					<span> </span>
					<span class="tabHelp">categories.attributes</span>
				</li>

				<li id="tabFilters" class="tab inactive" {denied role="filter"}style="display: none"{/denied}>
					<a href="{link controller=backend.filterGroup action=index id=_id_}">{t _filters}</a>
					<span> </span>
					<span class="tabHelp">categories.filters</span>
				</li>

				<li id="tabImages" class="tab inactive" {denied role="category"}style="display: none"{/denied}>
					<a href="{link controller=backend.categoryImage action=index id=_id_}">{t _images}</a>
					<span> </span>
					<span class="tabHelp">categories.images</span>
				</li>

				<li id="tabOptions" class="tab inactive" {denied role="option"}style="display: none"{/denied}>
					<a href="{link controller=backend.productOption action=index id=_id_ query="category=true"}">{t _options}</a>
					<span> </span>
					<span class="tabHelp">products.define</span>
				</li>

			</ul>
		</div>
		<div id="sectionContainer" class="sectionContainer maxHeight  h--50">
		</div>
	</div>

	<div id="addProductContainer" style="display: none;"></div>
</div>

<script type="text/javascript">
	{allowed role="category.sort"}
		Backend.Category.allowSorting = true;
	{/allowed}

	{allowed role="product"}
		Backend.Product.productsMiscPermision = true;
	{/allowed}

	Backend.showContainer('managerContainer');

	/**
	 * URL assigment for internal javascript requests
	 */
	Backend.Category.links = {literal}{}{/literal};
	Backend.Category.links.popup  = '{link controller=backend.category action=popup}';
	Backend.Category.links.create  = '{link controller=backend.category action=create id=_id_}';
	Backend.Category.links.remove  = '{link controller=backend.category action=remove id=_id_}';
	Backend.Category.links.countTabsItems = '{link controller=backend.category action=countTabsItems id=_id_}';
	Backend.Category.links.reorder = '{link controller=backend.category action=reorder id=_id_ query="parentId=_pid_&direction=_direction_"}';
	Backend.Category.links.categoryAutoloading = '{link controller=backend.category action=xmlBranch}';
	Backend.Category.links.categoryRecursiveAutoloading = '{link controller=backend.category action=xmlRecursivePath}';
	Backend.Category.links.addProduct  = '{link controller=backend.product action=add id=_id_}';

	Backend.Category.messages = {literal}{}{/literal};
	Backend.Category.messages._reorder_failed = '{t _reorder_failed|addslashes}';
	Backend.Category.messages._confirm_category_remove = '{t _confirm_category_remove|addslashes}';
	Backend.Category.messages._confirm_category_remove = '{t _confirm_category_remove|addslashes}';
	Backend.Category.messages._confirm_move = '{t _confirm_move|escape}';

	Backend.Category.init();

	Backend.Category.treeBrowser.setXMLAutoLoading(Backend.Category.links.categoryAutoloading);
	Backend.Category.addCategories({json array=$categoryList});
	CategoryTabControl.prototype.loadCategoryTabsCount({json array=$allTabsCount});

	Backend.Category.activeCategoryId = Backend.Category.treeBrowser.getSelectedItemId();
	Backend.Category.initPage();

	Backend.Category.loadBookmarkedCategory();

	Backend.Category.showControls();
</script>

{include file="backend/product/tabs.tpl"}

<script>
	Backend.Category.loadBookmarkedProduct();
</script>

<div id="specFieldSection"></div>

{include file="layout/backend/footer.tpl"}