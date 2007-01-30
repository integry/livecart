{includeJs file="library/livecart.js"}
{includeJs file="library/KeyboardEvent.js"}
{includeJs file="library/ActiveList.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/SectionExpander.js"}

{includeJs file="backend/Category.js"}
{includeJs file="backend/SpecField.js"}
{includeJs file="backend/Filter.js"}
{includeJs file="backend/CategoryImage.js"}

{* Calendar *}
{includeJs file="library/dhtmlCalendar/calendar.js"}
{includeJs file="library/dhtmlCalendar/lang/calendar-en.js"}
{includeJs file="library/dhtmlCalendar/lang/calendar-`$curLanguageCode`.js"}
{includeJs file="library/dhtmlCalendar/calendar-setup.js"}
{includeCss file="library/dhtmlCalendar/calendar-win2k-cold-2.css"}


{includeCss file="library/ActiveList.css"}
{includeCss file="backend/Category.css"}
{includeCss file="backend/SpecField.css"}
{includeCss file="backend/Filter.css"}
{includeCss file="backend/CategoryImage.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}


{pageTitle}Products and Categories{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="specField_item_blank" class="dom_template">{include file="backend/specField/form.tpl"}</div>
<div id="filter_item_blank" class="dom_template">{include file="backend/filter/form.tpl"}</div>


<div id="catgegoryContainer">
	<div id="categoryBrowser">
	</div>
	<div id="categoryBrowserActions">
		- <a href="javascript:Backend.Category.createNewBranch();">{t _create_subcategory}</a>
		<br/>
		- <a href="#" onclick="if (confirm('{t _confirm_category_remove|addslashes}')) Backend.Category.removeBranch(); return false;">{t _remove_category}</a>
	</div>
</div>

<div id="activeCategoryPath"></div>

<div id="managerContainer" class="maxHeight h--60">
	<div id="tabContainer">
		<ul id="tabList" class="tabs">
			<li id="tabProducts" class="tab active"><a href="{link controller=backend.product action=index id=_id_}">{t _products}</a><span> </span></li>
			<li id="tabMainDetails" class="tab inactive"><a href="{link controller=backend.category action=form id=_id_}">{t _category_details}</a><span> </span></li>
			<li id="tabFields" class="tab inactive"><a href="{link controller=backend.specField action=index id=_id_}">{t _attributes}</a><span> </span></li>
			<li id="tabFilters" class="tab inactive"><a href="{link controller=backend.filter action=index id=_id_}">{t _filters}</a><span> </span></li>
			<li id="tabImages" class="tab inactive"><a href="{link controller=backend.categoryImage action=index id=_id_}">{t _images}</a><span> </span></li>

<!--		<li id="tabArticles" class="tab inactive"><a href="{link controller=backend.image action=index id=_id_}">{t _articles}</a><span></span></li> -->
		</ul>
	</div>
	<div id="sectionContainer" class="maxHeight  h--50">
	</div>
</div>

<script type="text/javascript">
	/**
	 * URL assigment for internal javascript requests
	 */
    Backend.Category['links'] = {literal}{}{/literal};
	Backend.Category['links']['create']  = '{link controller=backend.category action=create id=_id_}';
	Backend.Category['links']['remove']  = '{link controller=backend.category action=remove id=_id_}';
	Backend.Category['links']['countTabsItems'] = '{link controller=backend.category action=countTabsItems id=_id_}';
	Backend.Category['links']['reorder'] = '{link controller=backend.category action=reorder id=_id_}/?parentId=_pid_';
	Backend.Category['links']['categoryAutoloading'] = '{link controller=backend.category action=xmlBranch}';
	Backend.Category['links']['categoryRecursiveAutoloading'] = '{link controller=backend.category action=xmlRecursivePath}';
    
    Backend.Category.messages = {literal}{}{/literal};
    Backend.Category.messages._reorder_failed = '{t _reorder_failed|addslashes}';

	Backend.Category.init();    
	Backend.Category.treeBrowser.setXMLAutoLoading(Backend.Category.links.categoryAutoloading); 
    Backend.Category.addCategories({json array=$categoryList});
    
	Backend.Category.activeCategoryId = Backend.Category.treeBrowser.getSelectedItemId();
	Backend.Category.initPage();
    
    Backend.Category.loadBookmarkedCategory();

	Backend.Category.image = new Backend.CategoryImage();

</script>

<div id="specFieldSection"></div>

{include file="layout/backend/footer.tpl"}
